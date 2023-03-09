<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace App\Console\Commands;

use Arr;
use DB;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportFromCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $buffers = [
        'movies' => [],
        'genres' => [],
        'genre_movie' => [],
        'companies' => [],
        'company_movie' => [],
        'keywords' => [],
        'keyword_movie' => [],
        'people' => [],
        'movie_person' => [],
    ];

    private int $inserted = 0;

    private string $message = '0';

    public function handle(): void
    {
        foreach (array_keys($this->buffers) as $table) {
            DB::table($table)->truncate();
        }
        $this->importMetadata();
        $this->importKeywords();
        $this->importPeople();
    }

    private function importPeople(): void
    {
        $this->info('Importing people');
        $reader = Reader::createFromPath(storage_path('app/csv/credits.csv'));
        $reader->setHeaderOffset(0);
        $people = collect();
        $progress = $this->getProgressBar($reader->count());
        try {
            foreach ($reader->getRecords() as $record) {
                $progress->setMessage($this->message);
                $progress->advance();
                $movie_id = Arr::get($record, 'id');
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'cast')) as $cast) {
                    $person_id = Arr::get($cast, 'id');
                    if (! $people->has($person_id)) {
                        $this->buffers['people'][] = [
                            'id' => $person_id,
                            'name' => Arr::get($cast, 'name'),
                            'profile' => Arr::get($cast, 'profile_path'),
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ];
                        $people->put($person_id, true);
                    }
                    $this->buffers['movie_person'][] = [
                        'movie_id' => $movie_id,
                        'person_id' => $person_id,
                        'job' => 'Actor',
                        'character' => Arr::get($cast, 'character'),
                        'order' => Arr::get($cast, 'order'),
                    ];
                }
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'crew')) as $crew) {
                    $person_id = Arr::get($crew, 'id');
                    if (! $people->has($person_id)) {
                        $this->buffers['people'][] = [
                            'id' => $person_id,
                            'name' => Arr::get($crew, 'name'),
                            'profile' => Arr::get($crew, 'profile_path'),
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ];
                        $people->put($person_id, true);
                    }
                    $this->buffers['movie_person'][] = [
                        'movie_id' => $movie_id,
                        'person_id' => $person_id,
                        'job' => Arr::get($crew, 'job'),
                        'character' => null,
                        'order' => null,
                    ];
                }
                $this->flushBuffers(500);
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            throw $e;
        } finally {
            $progress->clear();
        }
    }

    private function importKeywords(): void
    {
        $this->info('Importing keywords');
        $reader = Reader::createFromPath(storage_path('app/csv/keywords.csv'));
        $reader->setHeaderOffset(0);
        $keywords = collect();
        $progress = $this->getProgressBar($reader->count());
        try {
            foreach ($reader->getRecords() as $record) {
                $progress->setMessage($this->message);
                $progress->advance();
                $movie_id = Arr::get($record, 'id');
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'keywords')) as $keyword_data) {
                    $id = Arr::get($keyword_data, 'id');
                    if (! $keywords->has($id)) {
                        $this->buffers['keywords'][] = array_merge($keyword_data, [
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                        $keywords->put($id, true);
                    }
                    $this->buffers['keyword_movie'][] = [
                        'keyword_id' => $id,
                        'movie_id' => $movie_id,
                    ];
                }
                $this->flushBuffers(500);
            }
            $this->flushBuffers();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            throw $e;
        } finally {
            $progress->clear();
        }
    }

    private function importMetadata(): void
    {
        $this->info('Importing movies, genres and companies');
        $reader = Reader::createFromPath(storage_path('app/csv/movies_metadata.csv'));
        $reader->setHeaderOffset(0);
        $columns = $reader->getHeader();
        $movies = collect();
        $genres = collect();
        $companies = collect();
        $progress = $this->getProgressBar($reader->count());
        try {
            $records = $reader->getRecords();
            $incomplete = null;
            foreach ($records as $record) {
                $progress->setMessage($this->message);
                $progress->advance();
                if ($incomplete) {
                    $extra_values = array_slice(array_values($record), 0, 15);
                    $incomplete[9] .= array_shift($extra_values);
                    $record = array_combine($columns, array_merge($incomplete, $extra_values));
                    $incomplete = null;
                } elseif (Arr::get($record, 'popularity') === null) {
                    // Broken line.
                    $incomplete = array_slice(array_values($record), 0, 10);

                    continue;
                }
                $movie_id = Arr::get($record, 'id');
                if ($movies->has($movie_id)) {
                    continue;
                }
                $movies->put($movie_id, true);
                $this->buffers['movies'][] = [
                    'id' => $movie_id,
                    'title' => Arr::get($record, 'title'),
                    'tagline' => Arr::get($record, 'tagline'),
                    'description' => Arr::get($record, 'overview'),
                    'poster' => Arr::get($record, 'poster_path'),
                    'adult' => Arr::get($record, 'adult') === 'True',
                    'budget' => Arr::get($record, 'budget') ?: 0,
                    'revenue' => Arr::get($record, 'revenue') ?: 0,
                    'runtime' => Arr::get($record, 'runtime') ?: 0,
                    'popularity' => Arr::get($record, 'popularity') ?: 0,
                    'vote_average' => Arr::get($record, 'vote_average') ?: 0,
                    'vote_count' => Arr::get($record, 'vote_count') ?: 0,
                    'imdb_id' => Arr::get($record, 'imdb_id'),
                    'homepage' => Arr::get($record, 'homepage'),
                    'release_date' => Arr::get($record, 'release_date') ?: null,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'genres')) as $genre_data) {
                    $genre_id = $genre_data['id'];
                    if (! $genres->has($genre_id)) {
                        $this->buffers['genres'][] = array_merge($genre_data, [
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                        $genres->put($genre_id, true);
                    }
                    $this->buffers['genre_movie'][] = [
                        'genre_id' => $genre_id,
                        'movie_id' => $movie_id,
                    ];
                }
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'production_companies')) as $company_data) {
                    $company_id = $company_data['id'];

                    if (! $companies->has($company_id)) {
                        $this->buffers['companies'][] = array_merge($company_data, [
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                        $companies->put($company_id, true);
                    }
                    $this->buffers['company_movie'][] = [
                        'company_id' => $company_id,
                        'movie_id' => $movie_id,
                    ];
                }
                $this->flushBuffers(500);
            }
            $this->flushBuffers();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $progress->clear();
        }
    }

    private function flushBuffers(int $max = 0): void
    {
        foreach ($this->buffers as $table => $rows) {
            if (count($rows) > $max) {
                $this->inserted += count($rows);
                $this->message = number_format($this->inserted, 0, ',', '.');
                try {
                    DB::table($table)->insert($rows);
                } catch (\Throwable $e) {
                    if ($e->getCode() === '23000') {
                        foreach ($rows as $row) {
                            DB::table($table)->insertOrIgnore($row);
                        }
                    } else {
                        throw $e;
                    }
                }

                $this->buffers[$table] = [];
            }
        }
    }

    /**
     * @noinspection all
     */
    private function fixAndDecodeJson(string $json): array
    {
        $regex = <<<'REGEX'
~
    "[^"\\]*(?:\\.|[^"\\]*)*"
    (*SKIP)(*F)
  | '([^'\\]*(?:\\.|[^'\\]*)*)'
~x
REGEX;
        $fixed_quotes = preg_replace_callback($regex, function ($matches) {
            return '"'.preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]).'"';
        }, $json);
        $replacements = [
            "\'" => "'",
            '\\xa0' => '',
            '"profile_path": None' => '"profile_path": null',
            ': ""' => ': null',
        ];
        $fixed = str_replace(array_keys($replacements), array_values($replacements), $fixed_quotes);
        try {
            return json_decode($fixed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->output->writeln($fixed);
            throw $e;
        }
    }

    private function getProgressBar(int $count): ProgressBar
    {
        $progress = $this->output->createProgressBar($count);
        ProgressBar::setFormatDefinition('debug-message', ProgressBar::getFormatDefinition('debug').' | Inserts: %message%');
        $progress->setFormat('debug-message');
        $progress->setMessage(number_format($this->inserted, 0, ',', '.'));
        $progress->setRedrawFrequency(1);

        return $progress;
    }
}

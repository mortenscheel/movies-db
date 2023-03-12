<?php

/** @noinspection DisconnectedForeachInstructionInspection */

/** @noinspection PhpExceptionImmediatelyRethrownInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace App\Console\Commands;

use App\TmdbJsonFixer;
use function array_slice;
use function count;
use DB;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;
use ZipArchive;

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

    private array $tables = [
        'movies' => [
            'buffer' => [],
            'inserts' => 0,
            'processed' => [],
        ],
        'genres' => [
            'buffer' => [],
            'inserts' => 0,
            'processed' => [],
        ],
        'genre_movie' => [
            'buffer' => [],
            'inserts' => 0,
        ],
        'companies' => [
            'buffer' => [],
            'inserts' => 0,
            'processed' => [],
        ],
        'company_movie' => [
            'buffer' => [],
            'inserts' => 0,
        ],
        'keywords' => [
            'buffer' => [],
            'inserts' => 0,
            'processed' => [],
        ],
        'keyword_movie' => [
            'buffer' => [],
            'inserts' => 0,
        ],
        'people' => [
            'buffer' => [],
            'inserts' => 0,
            'processed' => [],
        ],
        'cast' => [
            'buffer' => [],
            'inserts' => 0,
        ],
        'crew' => [
            'buffer' => [],
            'inserts' => 0,
        ],
    ];

    private TmdbJsonFixer $fixer;

    private ProgressBar $bar;

    public function handle(): int
    {
        $this->fixer = new TmdbJsonFixer();
        ProgressBar::setFormatDefinition('custom', '%message%');
        $this->bar = $this->output->createProgressBar();
        $this->bar->setFormat('custom');
        foreach (array_keys($this->tables) as $table) {
            DB::table($table)->truncate();
        }
        if (! File::exists(storage_path('app/csv'))) {
            $this->extractArchive();
        }
        $this->importMetadata();
        $this->importKeywords();
        $this->importCredits();
        $this->bar->clear();

        return self::SUCCESS;
    }

    private function extractArchive(): void
    {
        $this->task('Extracting storage/app/archive.zip', function () {
            $zipPath = storage_path('app/archive.zip');
            if (! File::exists($zipPath)) {
                $this->error('storage/app/archive.zip is missing. Check readme for instructions.');

                return self::FAILURE;
            }
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                return false;
            }
            $zip->extractTo(storage_path('app/csv'), ['movies_metadata.csv', 'credits.csv', 'keywords.csv']);

            return true;
        });
    }

    private function importMetadata(): void
    {
        $this->bar->clear();
        $this->info('Processing movies_metadata.csv');
        $this->bar->display();
        $reader = Reader::createFromPath(storage_path('app/csv/movies_metadata.csv'));
        $reader->setHeaderOffset(0);
        $columns = $reader->getHeader();
        $incomplete = null;
        foreach ($reader->getRecords() as $record) {
            $this->advance();
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
            if (! $this->isProcessed('movies', $movie_id)) {
                $this->buffer('movies', [
                    'id' => $movie_id,
                    'user_id' => 1,
                    'title' => Arr::get($record, 'title'),
                    'tagline' => Arr::get($record, 'tagline'),
                    'description' => Arr::get($record, 'overview'),
                    'poster' => Arr::get($record, 'poster_path'),
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
                ]);
            }
            foreach ($this->fixer->fix(Arr::get($record, 'genres')) as $genre_data) {
                $genre_id = $genre_data['id'];
                if (! $this->isProcessed('genres', $genre_id)) {
                    $this->buffer('genres', array_merge($genre_data, [
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ]));
                }
                $this->buffer('genre_movie', [
                    'genre_id' => $genre_id,
                    'movie_id' => $movie_id,
                ]);
            }
            foreach ($this->fixer->fix(Arr::get($record, 'production_companies')) as $company_data) {
                $company_id = $company_data['id'];
                if (! $this->isProcessed('companies', $company_id)) {
                    $this->buffer('companies', array_merge($company_data, [
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ]));
                }
                $this->buffer('company_movie', [
                    'company_id' => $company_id,
                    'movie_id' => $movie_id,
                ]);
            }
            $this->flushBuffers(50);
        }
        $this->flushBuffers();
    }

    private function importKeywords(): void
    {
        $this->bar->clear();
        $this->info('Processing keywords.csv');
        $this->bar->display();
        $reader = Reader::createFromPath(storage_path('app/csv/keywords.csv'));
        $reader->setHeaderOffset(0);
        foreach ($reader->getRecords() as $record) {
            $this->advance();
            $movie_id = Arr::get($record, 'id');
            foreach ($this->fixer->fix(Arr::get($record, 'keywords')) as $keyword_data) {
                $id = Arr::get($keyword_data, 'id');
                if (! $this->isProcessed('keywords', $id)) {
                    $this->buffer('keywords', array_merge($keyword_data, [
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ]));
                }
                $this->buffer('keyword_movie', [
                    'keyword_id' => $id,
                    'movie_id' => $movie_id,
                ]);
            }
            $this->flushBuffers(50);
        }
        $this->flushBuffers();
    }

    private function importCredits(): void
    {
        $this->bar->clear();
        $this->info('Processing credits.csv');
        $this->bar->display();
        $reader = Reader::createFromPath(storage_path('app/csv/credits.csv'));
        $reader->setHeaderOffset(0);
        $timestamp = now()->toDateTimeString();
        foreach ($reader->getRecords() as $record) {
            $this->advance();
            $movie_id = Arr::get($record, 'id');
            foreach ($this->fixer->fix(Arr::get($record, 'cast')) as $cast) {
                $person_id = Arr::get($cast, 'id');
                if (! $this->isProcessed('people', $person_id)) {
                    $this->buffer('people', [
                        'id' => $person_id,
                        'name' => Arr::get($cast, 'name'),
                        'profile' => Arr::get($cast, 'profile_path'),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
                $this->buffer('cast', [
                    'movie_id' => $movie_id,
                    'person_id' => $person_id,
                    'cast_id' => Arr::get($cast, 'cast_id'),
                    'character' => Arr::get($cast, 'character'),
                    'order' => Arr::get($cast, 'order'),
                ]);
            }
            foreach ($this->fixer->fix(Arr::get($record, 'crew')) as $crew) {
                $person_id = Arr::get($crew, 'id');
                if (! $this->isProcessed('people', $person_id)) {
                    $this->buffer('people', [
                        'id' => $person_id,
                        'name' => Arr::get($crew, 'name'),
                        'profile' => Arr::get($crew, 'profile_path'),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
                $this->buffer('crew', [
                    'movie_id' => $movie_id,
                    'person_id' => $person_id,
                    'job' => Arr::get($crew, 'job'),
                ]);
            }
            $this->flushBuffers(50);
        }
        $this->flushBuffers();
    }

    private function flushBuffers(int $max = 0): void
    {
        foreach ($this->tables as $table => $data) {
            $rows = $data['buffer'];
            if (count($rows) > $max) {
                $this->tables[$table]['inserts'] += count($rows);
                try {
                    DB::table($table)->insert($rows);
                } catch (Throwable $e) {
                    if ($e->getCode() === '23000') {
                        foreach ($rows as $row) {
                            DB::table($table)->insertOrIgnore($row);
                        }
                    } else {
                        throw $e;
                    }
                }

                $this->tables[$table]['buffer'] = [];
            }
        }
    }

    private function buffer(string $table, array $row): void
    {
        $this->tables[$table]['buffer'][] = $row;
    }

    private function isProcessed(string $table, int $id): bool
    {
        if (Arr::has($this->tables, "$table.processed.$id")) {
            return true;
        }
        Arr::set($this->tables, "$table.processed.$id", true);

        return false;
    }

    private function advance(): void
    {
        $this->bar->setMessage(
            collect($this->tables)->map(fn ($data, $table) => sprintf('%-15s%10s', $table, number_format($data['inserts'], thousands_separator: '.')))->join("\n")
        );
        $this->bar->advance();
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Genre;
use Arr;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use League\Csv\Reader;

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
        'genre_movie' => [],
        'company_movie' => [],
    ];

    /**
     * Execute the console command.
     *
     * @throws \JsonException
     */
    public function handle(): void
    {
        $tables = [
            'movies',
            'genres',
            'companies',
            'genre_movie',
            'company_movie',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        $this->importMetadata();
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function importMetadata(): void
    {
        $this->info('Importing movies, genres and companies');
        $reader = Reader::createFromPath(storage_path('app/csv/movies_metadata.csv'));
        $reader->setHeaderOffset(0);
        $columns = $reader->getHeader();
        $movies = collect();
        $genres = collect();
        $companies = collect();
        $progress = $this->output->createProgressBar($reader->count());
        $progress->setFormat('debug');
        $line = 0;
        try {
            $records = $reader->getRecords();
            $incomplete = null;
            foreach ($records as $record) {
                $line++;
                if ($incomplete) {
                    $extra_values = array_slice(array_values($record), 0, 15);
                    $incomplete[9] .= array_shift($extra_values);
                    $record = array_combine($columns, array_merge($incomplete, $extra_values));
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
                ];
                foreach ($this->fixAndDecodeJson(Arr::get($record, 'genres')) as $genre_data) {
                    $genre_id = $genre_data['id'];
                    if (! $genres->has($genre_id)) {
                        Genre::create($genre_data);
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
                        Company::create($company_data);
                        $companies->put($company_id, true);
                    }
                    $this->buffers['company_movie'][] = [
                        'company_id' => $company_id,
                        'movie_id' => $movie_id,
                    ];
                }
                $this->flushBuffers(300);
                $progress->advance();
                $a = 0;
            }
            $this->flushBuffers();
        } catch (\Throwable $e) {
            $a = 0;
            throw $e;
        } finally {
            $progress->clear();
        }
    }

    private function flushBuffers(int $max = 0): void
    {
        foreach ($this->buffers as $table => $rows) {
            if (count($rows) > $max) {
                DB::table($table)->insert($rows);
                $this->buffers[$table] = [];
            }
        }
    }

    /**
     * @noinspection all
     */
    private function fixAndDecodeJson(string $json): Collection
    {
        $regex = <<<'REGEX'
~
    "[^"\\]*(?:\\.|[^"\\]*)*"
    (*SKIP)(*F)
  | '([^'\\]*(?:\\.|[^'\\]*)*)'
~x
REGEX;
        $fixed = preg_replace_callback($regex, function ($matches) {
            return '"'.preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]).'"';
        }, $json);
        $fixed = str_replace('\\xa0', '', $fixed);

        return collect(json_decode($fixed, true, 512, JSON_THROW_ON_ERROR));
    }
}

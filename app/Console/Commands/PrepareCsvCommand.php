<?php

namespace App\Console\Commands;

use App\TmdbJsonFixer;
use function array_key_exists;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use League\Csv\Reader;
use League\Csv\Writer;

class PrepareCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection DisconnectedForeachInstructionInspection
     */
    public function handle(): void
    {
        $fixer = new TmdbJsonFixer();
        $timestamp = now()->toDateTimeString();
        $reader = Reader::createFromPath(storage_path('app/csv/credits.csv'));
        $reader->setHeaderOffset(0);
        $people_csv = Writer::createFromPath(storage_path('app/csv/people.csv'), 'wb');
        $people_csv->insertOne(['id', 'name', 'profile', 'created_at', 'updated_at']);
        $cast_csv = Writer::createFromPath(storage_path('app/csv/cast.csv'), 'wb');
        $cast_csv->insertOne(['movie_id', 'person_id', 'cast_id', 'character', 'order']);
        $crew_csv = Writer::createFromPath(storage_path('app/csv/crew.csv'), 'wb');
        $crew_csv->insertOne(['movie_id', 'person_id', 'job']);
        $bar = $this->output->createProgressBar(45476);
        $bar->setFormat('debug');
        $processed = [];
        foreach ($reader->getRecords() as $record) {
            $bar->advance();
            $movie_id = Arr::get($record, 'id');
            foreach ($fixer->fix(Arr::get($record, 'cast')) as $cast) {
                $person_id = Arr::get($cast, 'id');
                $person = [
                    'id' => $person_id,
                    'name' => Arr::get($cast, 'name'),
                    'profile' => Arr::get($cast, 'profile_path'),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                if (! array_key_exists($person_id, $processed)) {
                    $processed[$person_id] = true;
                    $people_csv->insertOne($person);
                }
                $cast_csv->insertOne([
                    'movie_id' => $movie_id,
                    'person_id' => $person_id,
                    'cast_id' => Arr::get($cast, 'cast_id'),
                    'character' => Arr::get($cast, 'character'),
                    'order' => Arr::get($cast, 'order'),
                ]);
            }
            foreach ($fixer->fix(Arr::get($record, 'crew')) as $crew) {
                $person_id = Arr::get($crew, 'id');
                $person = [
                    'id' => $person_id,
                    'name' => Arr::get($crew, 'name'),
                    'profile' => Arr::get($crew, 'profile_path'),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                if (! array_key_exists($person_id, $processed)) {
                    $processed[$person_id] = true;
                    $people_csv->insertOne($person);
                }
                $crew_csv->insertOne([
                    'movie_id' => $movie_id,
                    'person_id' => $person_id,
                    'job' => Arr::get($crew, 'job'),
                ]);
            }
        }
        $bar->clear();
    }
}

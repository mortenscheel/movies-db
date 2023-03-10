<?php

namespace App;

use Illuminate\Support\Facades\File;

class TmdbJsonFixer
{
    private string $regex = <<<'REGEX'
~
    "[^"\\]*(?:\\.|[^"\\]*)*"
    (*SKIP)(*F)
  | '([^'\\]*(?:\\.|[^'\\]*)*)'
~x
REGEX;

    private array $replacements = [
        "\'" => "'",
        '\\xa0' => '',
        '\\xa1' => '',
        '\\xa2' => '',
        '\\xa3' => '',
        '\\xa4' => '',
        '\\xa5' => '',
        '\\xa6' => '',
        '\\xa7' => '',
        '\\xa8' => '',
        '\\xa9' => '',
        '\\xaa' => '',
        '\\xab' => '',
        '\\xac' => '',
        '\\xad' => '',
        '\\xae' => '',
        '\\xaf' => '',
        '\\x92' => "'",
        '"profile_path": None' => '"profile_path": null',
        ': ""' => ': null',
    ];

    /**
     * @throws \JsonException
     */
    public function fix(string $json): array
    {
        $fixed = str_replace(array_keys($this->replacements), array_values($this->replacements), $this->fixSingleQuotes($json));
        try {
            return json_decode($fixed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            File::put(storage_path('app/json-error.json'), $fixed);
            fwrite(STDERR, "\n".$e->getMessage()."\nFixed string stored as storage/app/json-error.json");
            throw $e;
        }
    }

    private function fixSingleQuotes(string $json): string
    {
        return preg_replace_callback($this->regex, function ($matches) {
            return '"'.preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]).'"';
        }, $json);
    }
}

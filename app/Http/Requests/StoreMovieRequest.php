<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|unique:movies,title',
            'tagline' => 'nullable|string',
            'description' => 'required|string',
            'poster' => 'nullable|string',
            'budget' => 'required|int|min:0',
            'revenue' => 'required|int|min:0',
            'runtime' => 'required|numeric|min:0',
            'popularity' => 'required|numeric|min:0',
            'vote_average' => 'required|numeric|min:0|max:10',
            'vote_count' => 'required|int|min:0',
            'imdb_id' => 'required|string',
            'homepage' => 'nullable|url',
            'release_date' => 'nullable|date_format:Y-m-d',
        ];
    }
}

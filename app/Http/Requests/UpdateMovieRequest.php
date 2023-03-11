<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|unique:movies,title,'.$this->route('movie')?->id,
            'tagline' => 'sometimes|string',
            'description' => 'sometimes|string',
            'poster' => 'sometimes|string',
            'budget' => 'sometimes|int|min:0',
            'revenue' => 'sometimes|int|min:0',
            'runtime' => 'sometimes|numeric|min:0',
            'popularity' => 'sometimes|numeric|min:0',
            'vote_average' => 'sometimes|numeric|min:0|max:10',
            'vote_count' => 'sometimes|int|min:0',
            'imdb_id' => 'sometimes|string',
            'homepage' => 'sometimes|url',
            'release_date' => 'sometimes|date_format:Y-m-d',
        ];
    }
}

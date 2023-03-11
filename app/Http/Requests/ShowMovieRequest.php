<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\MovieController;
use Illuminate\Foundation\Http\FormRequest;

class ShowMovieRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'include' => 'nullable|string',
            'fields' => 'nullable|string',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'include' => [
                'description' => 'Relationships to include (comma separated). Allowed values: '.collect(MovieController::getAllowedIncludes())->join(', '),
                'example' => 'genres,cast',
            ],
            'fields' => [
                'description' => 'Fields to include (comma separated). Allowed values: '.collect(MovieController::getAllowedFields())->join(', '),
                'example' => 'No-example',
            ],
        ];
    }
}

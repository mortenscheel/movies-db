<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\MovieController;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters
 */
#[QueryParam('filter[id]', description: 'Filter by id (csv)', required: false, example: 'No-example')]
#[QueryParam('filter[title]', description: 'Filter by title (csv)', required: false, example: 'No-example')]
class IndexMovieRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'sometimes|int|min:1',
            'per_page' => 'sometimes|int|min:1|max:100',
            'filter' => 'sometimes|array',
            'include' => 'sometimes|string',
            'fields' => 'nullable|string',
            'sort' => 'nullable|string',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'page' => [
                'description' => 'Pagination page',
                'type' => 'int',
                'example' => 1,
            ],
            'per_page' => [
                'description' => 'Number of movies per page',
                'type' => 'int',
                'example' => 15,
            ],
            'include' => [
                'description' => 'Relationships to include (comma separated). Allowed values: '.collect(MovieController::getAllowedIncludes())->join(', '),
                'example' => 'genres,cast',
            ],
            'fields' => [
                'description' => 'Fields to include (comma separated). Allowed values: '.collect(MovieController::getAllowedFields())->join(', '),
                'example' => 'No-example',
            ],
            'sort' => [
                'description' => 'Fields to sort by (comma separated) (prefix - for descending). Allowed values: '.collect(MovieController::getAllowedSorts())->join(', '),
                'example' => 'No-example',
            ],
        ];
    }
}

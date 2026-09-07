<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateBookRequest extends StoreBookRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Allow partial updates: every rule from the store request becomes optional.
        $rules = [];

        foreach (parent::rules() as $field => $constraints) {
            $constraints = (array) $constraints;
            $constraints = array_values(array_filter($constraints, fn ($rule) => $rule !== 'required'));
            array_unshift($constraints, 'sometimes');
            $rules[$field] = $constraints;
        }

        $book = $this->route('book');

        $rules['isbn'] = ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book)];

        return $rules;
    }
}

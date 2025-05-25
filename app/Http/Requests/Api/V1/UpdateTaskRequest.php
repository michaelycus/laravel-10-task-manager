<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'due_date'          => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'priority'          => ['sometimes', 'required', Rule::in(['low', 'medium', 'high'])],
            'category_ids'      => ['sometimes', 'nullable', 'array'],
            'category_ids.*'    => ['integer', 'exists:categories,id'],
            'mark_as_completed' => ['sometimes', 'boolean'], // To toggle completion
        ];
    }
}

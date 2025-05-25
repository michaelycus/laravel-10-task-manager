<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category') ? $this->route('category')->id : null;
        return [
            'name'        => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug'        => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId), 'alpha_dash'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        // If name is being updated and slug is not provided, regenerate slug
        if ($this->name && ! $this->slug && $this->isMethod('put') || $this->isMethod('patch')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}

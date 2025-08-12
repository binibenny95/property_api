<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {

        $rules = [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:nodes,id',
            'type' => 'required|in:Corporation,Building,Property,Tenancy Period,Tenant',
            'relationship_to_parent' => 'nullable|string|max:255',
        ];

        // Add conditional rules based on type
        if ($this->input('type') === 'Building') {
            $rules['zip_code'] = 'required|string|max:10';
        }

        if ($this->input('type') === 'Property') {
            $rules['monthly_rent'] = 'required|numeric|min:0';
        }

        if ($this->input('type') === 'Tenancy Period') {
            $rules['tenancy_active'] = 'required|boolean';
        }

        if ($this->input('type') === 'Tenant') {
            $rules['move_in_date'] = 'required|date';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'zip_code.required' => 'Zip code is required when creating a Building.',
            'monthly_rent.required' => 'Monthly rent is required when creating a Property.',
            'tenancy_active.required' => 'Tenancy active status is required when creating a Tenancy Period.',
            'move_in_date.required' => 'Move in date is required when creating a Tenant.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::info('=== VALIDATION FAILED ===', [
            'errors' => $validator->errors()->toArray()
        ]);

        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422)
        );
    }

}

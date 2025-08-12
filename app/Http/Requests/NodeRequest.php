<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:nodes,id',
            'type' => 'required|in:Corporation,Building,Property,Tenancy Period,Tenant',
            'relationship_to_parent' => 'nullable|string|max:255',
            'zip_code' => 'required_if:type,Building',
            'monthly_rent' => 'required_if:type,Property|numeric|nullable',
            'tenancy_active' => 'required_if:type,Tenancy Period|boolean',
            'move_in_date' => 'required_if:type,Tenant|date',
        ];
    }
}

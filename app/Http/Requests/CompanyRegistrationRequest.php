<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRegistrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'sector_id' => 'required|exists:business_sectors,id',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'logo' => 'nullable|string',
            'registration_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'employee_count' => 'nullable|integer|min:1',
            'annual_revenue' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Company name is required',
            'description.required' => 'Company description is required',
            'sector_id.required' => 'Business sector is required',
            'sector_id.exists' => 'Selected business sector is invalid',
            'phone.required' => 'Phone number is required',
            'email.required' => 'Email address is required',
            'address.required' => 'Company address is required',
            'country.required' => 'Country is required',
            'city.required' => 'City is required',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'job_id' => 'required|exists:jobs,id',
            'cover_letter' => 'required|string|max:2000',
            'resume_file' => 'nullable|string',
            'expected_salary' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'availability_date' => 'nullable|date|after_or_equal:today',
            'notice_period' => 'nullable|string|max:100',
            
            // Personal Information
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',
            
            // Education
            'education' => 'required|array|min:1',
            'education.*.degree' => 'required|string|max:100',
            'education.*.institution' => 'required|string|max:200',
            'education.*.field_of_study' => 'nullable|string|max:100',
            'education.*.start_year' => 'required|integer|min:1950|max:' . date('Y'),
            'education.*.end_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 10),
            'education.*.grade' => 'nullable|string|max:20',
            
            // Work Experience
            'experience' => 'nullable|array',
            'experience.*.job_title' => 'required|string|max:100',
            'experience.*.company' => 'required|string|max:200',
            'experience.*.location' => 'nullable|string|max:100',
            'experience.*.start_date' => 'required|date',
            'experience.*.end_date' => 'nullable|date|after:experience.*.start_date',
            'experience.*.is_current' => 'boolean',
            'experience.*.description' => 'nullable|string|max:1000',
            
            // Skills
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            
            // Languages
            'languages' => 'nullable|array',
            'languages.*.language' => 'required|string|max:50',
            'languages.*.proficiency' => 'required|in:basic,intermediate,advanced,native',
            
            // Certifications
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required|string|max:200',
            'certifications.*.issuer' => 'required|string|max:200',
            'certifications.*.issue_date' => 'required|date',
            'certifications.*.expiry_date' => 'nullable|date|after:certifications.*.issue_date',
            'certifications.*.credential_id' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'job_id.required' => 'Job selection is required',
            'cover_letter.required' => 'Cover letter is required',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email address is required',
            'phone.required' => 'Phone number is required',
            'address.required' => 'Address is required',
            'city.required' => 'City is required',
            'country.required' => 'Country is required',
            'education.required' => 'At least one education entry is required',
            'education.*.degree.required' => 'Degree is required for each education entry',
            'education.*.institution.required' => 'Institution is required for each education entry',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegionalAgentProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => 'nullable|string|max:255',
            'business_description' => 'nullable|string|max:5000',
            'business_license' => 'nullable|string|max:255',
            'region_type' => 'required|in:global,country,state,city',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'service_types' => 'nullable|array',
            'service_types.*' => 'string|max:255',
            'supported_categories' => 'nullable|array',
            'supported_categories.*' => 'string|max:255',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'office_address' => 'nullable|string|max:500',
            'office_phone' => 'nullable|string|max:50',
            'office_email' => 'nullable|email|max:255',
            'office_hours' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'timezone' => 'nullable|string|max:50',
            'logo' => 'nullable|file|image|max:5120|mimes:jpeg,jpg,png,webp',
            'video_resume' => 'nullable|file|mimes:mp4,webm,ogg|max:102400', // 100MB max for video
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'service_fee' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'video_resume.max' => 'The video resume must not be larger than 100MB.',
            'video_resume.mimes' => 'The video resume must be a file of type: mp4, webm, ogg.',
            'logo.max' => 'The logo must not be larger than 5MB.',
            'region_type.required' => 'Please select a region type.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FormController extends Controller
{
    /**
     * Get company registration form structure
     */
    public function companyRegistrationForm(): JsonResponse
    {
        return response()->json([
            'form_name' => 'Company Registration',
            'sections' => [
                [
                    'title' => 'Basic Information',
                    'fields' => [
                        ['name' => 'name', 'type' => 'text', 'label' => 'Company Name', 'required' => true],
                        ['name' => 'description', 'type' => 'textarea', 'label' => 'Company Description', 'required' => true],
                        ['name' => 'sector_id', 'type' => 'select', 'label' => 'Business Sector', 'required' => true, 'endpoint' => '/api/v1/public/sectors'],
                        ['name' => 'industry', 'type' => 'text', 'label' => 'Industry', 'required' => false],
                        ['name' => 'website', 'type' => 'url', 'label' => 'Website', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Contact Information',
                    'fields' => [
                        ['name' => 'phone', 'type' => 'tel', 'label' => 'Phone Number', 'required' => true],
                        ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true],
                        ['name' => 'address', 'type' => 'textarea', 'label' => 'Address', 'required' => true],
                        ['name' => 'city', 'type' => 'text', 'label' => 'City', 'required' => true],
                        ['name' => 'country', 'type' => 'select', 'label' => 'Country', 'required' => true, 'endpoint' => '/api/v1/public/countries'],
                        ['name' => 'postal_code', 'type' => 'text', 'label' => 'Postal Code', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Company Details',
                    'fields' => [
                        ['name' => 'registration_number', 'type' => 'text', 'label' => 'Registration Number', 'required' => false],
                        ['name' => 'tax_id', 'type' => 'text', 'label' => 'Tax ID', 'required' => false],
                        ['name' => 'founded_year', 'type' => 'number', 'label' => 'Founded Year', 'required' => false],
                        ['name' => 'employee_count', 'type' => 'number', 'label' => 'Number of Employees', 'required' => false],
                        ['name' => 'annual_revenue', 'type' => 'number', 'label' => 'Annual Revenue', 'required' => false],
                        ['name' => 'currency', 'type' => 'select', 'label' => 'Currency', 'required' => false, 'options' => ['USD', 'EUR', 'GBP', 'AED']],
                        ['name' => 'logo', 'type' => 'file', 'label' => 'Company Logo', 'required' => false],
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get job application form structure
     */
    public function jobApplicationForm(): JsonResponse
    {
        return response()->json([
            'form_name' => 'Job Application',
            'sections' => [
                [
                    'title' => 'Application Details',
                    'fields' => [
                        ['name' => 'cover_letter', 'type' => 'textarea', 'label' => 'Cover Letter', 'required' => true],
                        ['name' => 'resume_file', 'type' => 'file', 'label' => 'Resume/CV', 'required' => false],
                        ['name' => 'expected_salary', 'type' => 'number', 'label' => 'Expected Salary', 'required' => false],
                        ['name' => 'currency', 'type' => 'select', 'label' => 'Currency', 'required' => false, 'options' => ['USD', 'EUR', 'GBP', 'AED']],
                        ['name' => 'availability_date', 'type' => 'date', 'label' => 'Availability Date', 'required' => false],
                        ['name' => 'notice_period', 'type' => 'text', 'label' => 'Notice Period', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Personal Information',
                    'fields' => [
                        ['name' => 'first_name', 'type' => 'text', 'label' => 'First Name', 'required' => true],
                        ['name' => 'last_name', 'type' => 'text', 'label' => 'Last Name', 'required' => true],
                        ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                        ['name' => 'phone', 'type' => 'tel', 'label' => 'Phone', 'required' => true],
                        ['name' => 'address', 'type' => 'textarea', 'label' => 'Address', 'required' => true],
                        ['name' => 'city', 'type' => 'text', 'label' => 'City', 'required' => true],
                        ['name' => 'country', 'type' => 'select', 'label' => 'Country', 'required' => true, 'endpoint' => '/api/v1/public/countries'],
                        ['name' => 'postal_code', 'type' => 'text', 'label' => 'Postal Code', 'required' => false],
                        ['name' => 'date_of_birth', 'type' => 'date', 'label' => 'Date of Birth', 'required' => false],
                        ['name' => 'nationality', 'type' => 'text', 'label' => 'Nationality', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Education',
                    'type' => 'repeatable',
                    'fields' => [
                        ['name' => 'degree', 'type' => 'text', 'label' => 'Degree', 'required' => true],
                        ['name' => 'institution', 'type' => 'text', 'label' => 'Institution', 'required' => true],
                        ['name' => 'field_of_study', 'type' => 'text', 'label' => 'Field of Study', 'required' => false],
                        ['name' => 'start_year', 'type' => 'number', 'label' => 'Start Year', 'required' => true],
                        ['name' => 'end_year', 'type' => 'number', 'label' => 'End Year', 'required' => false],
                        ['name' => 'grade', 'type' => 'text', 'label' => 'Grade/GPA', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Work Experience',
                    'type' => 'repeatable',
                    'fields' => [
                        ['name' => 'job_title', 'type' => 'text', 'label' => 'Job Title', 'required' => true],
                        ['name' => 'company', 'type' => 'text', 'label' => 'Company', 'required' => true],
                        ['name' => 'location', 'type' => 'text', 'label' => 'Location', 'required' => false],
                        ['name' => 'start_date', 'type' => 'date', 'label' => 'Start Date', 'required' => true],
                        ['name' => 'end_date', 'type' => 'date', 'label' => 'End Date', 'required' => false],
                        ['name' => 'is_current', 'type' => 'checkbox', 'label' => 'Current Position', 'required' => false],
                        ['name' => 'description', 'type' => 'textarea', 'label' => 'Job Description', 'required' => false],
                    ]
                ],
                [
                    'title' => 'Skills & Languages',
                    'fields' => [
                        ['name' => 'skills', 'type' => 'tags', 'label' => 'Skills', 'required' => false],
                        ['name' => 'languages', 'type' => 'repeatable', 'label' => 'Languages', 'required' => false, 'fields' => [
                            ['name' => 'language', 'type' => 'text', 'label' => 'Language', 'required' => true],
                            ['name' => 'proficiency', 'type' => 'select', 'label' => 'Proficiency', 'required' => true, 'options' => ['basic', 'intermediate', 'advanced', 'native']],
                        ]],
                    ]
                ],
                [
                    'title' => 'Certifications',
                    'type' => 'repeatable',
                    'fields' => [
                        ['name' => 'name', 'type' => 'text', 'label' => 'Certification Name', 'required' => true],
                        ['name' => 'issuer', 'type' => 'text', 'label' => 'Issuing Organization', 'required' => true],
                        ['name' => 'issue_date', 'type' => 'date', 'label' => 'Issue Date', 'required' => true],
                        ['name' => 'expiry_date', 'type' => 'date', 'label' => 'Expiry Date', 'required' => false],
                        ['name' => 'credential_id', 'type' => 'text', 'label' => 'Credential ID', 'required' => false],
                    ]
                ]
            ]
        ]);
    }
}
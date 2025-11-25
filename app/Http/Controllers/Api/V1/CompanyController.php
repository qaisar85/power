<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CompanyRegistrationRequest;

class CompanyController extends Controller
{
    /**
     * Display user's companies
     */
    public function index(Request $request): JsonResponse
    {
        $companies = $request->user()->companies()
            ->latest()
            ->paginate(20);

        return response()->json($companies);
    }

    /**
     * Display the specified company
     */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json($company);
    }

    /**
     * Store a newly created company
     */
    public function store(CompanyRegistrationRequest $request): JsonResponse
    {
        $company = $request->user()->companies()->create(array_merge(
            $request->validated(),
            ['status' => 'pending']
        ));

        $company->load('sector');

        return response()->json([
            'message' => 'Company registration submitted successfully',
            'company' => $company
        ], 201);
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $company->update($validator->validated());

        return response()->json($company);
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }

    /**
     * Request verification for company
     */
    public function requestVerification(Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        if ($company->status === 'verified') {
            return response()->json(['message' => 'Company is already verified'], 400);
        }

        if ($company->status === 'pending_verification') {
            return response()->json(['message' => 'Verification request already submitted'], 400);
        }

        $company->update(['status' => 'pending_verification']);

        return response()->json([
            'message' => 'Verification request submitted successfully',
            'company' => $company
        ]);
    }
}
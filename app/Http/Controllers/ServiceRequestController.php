<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $requests = ServiceRequest::with(['service', 'user'])
            ->whereHas('service', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->orderByDesc('id')
            ->paginate(20);

        return Inertia::render('ServiceDashboard/Requests/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'requests' => $requests,
        ]);
    }

    public function accept(Request $request, ServiceRequest $requestModel)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($requestModel->service->company_id === $company->id, 403);
        $requestModel->update(['status' => 'in_progress']);
        return back()->with('success', 'Request accepted and marked in progress.');
    }

    public function reject(Request $request, ServiceRequest $requestModel)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($requestModel->service->company_id === $company->id, 403);
        $requestModel->update(['status' => 'rejected']);
        return back()->with('success', 'Request rejected.');
    }

    public function reply(Request $request, ServiceRequest $requestModel)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($requestModel->service->company_id === $company->id, 403);

        $data = $request->validate([
            'message' => 'required|string',
            'price' => 'nullable|numeric',
            'complete_by' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $replyFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('service_requests/replies/'.$requestModel->id, 'public');
                $replyFiles[] = $path;
            }
        }

        $requestModel->update([
            'reply' => [
                'message' => $data['message'],
                'price' => $data['price'] ?? null,
                'complete_by' => $data['complete_by'] ?? null,
                'attachments' => $replyFiles,
            ],
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Reply sent to client.');
    }
}
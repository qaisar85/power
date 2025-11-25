<?php

namespace App\Http\Controllers;

use App\Models\KycVerification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminKycController extends Controller
{
    public function index(Request $request)
    {
        $items = KycVerification::orderByDesc('created_at')->paginate(30);
        return Inertia::render('Admin/Kyc/Index', [
            'items' => $items,
        ]);
    }

    public function approve(Request $request, KycVerification $kyc)
    {
        $kyc->status = 'approved';
        $kyc->moderator_id = auth()->guard('admin')->id();
        $kyc->save();
        return redirect()->back()->with('success', 'KYC approved');
    }

    public function reject(Request $request, KycVerification $kyc)
    {
        $kyc->status = 'rejected';
        $kyc->moderator_id = auth()->guard('admin')->id();
        $kyc->notes = (string) $request->input('notes');
        $kyc->save();
        return redirect()->back()->with('success', 'KYC rejected');
    }
}


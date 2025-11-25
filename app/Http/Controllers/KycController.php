<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\KycVerification;

class KycController extends Controller
{
    public function index(Request $request)
    {
        $kyc = KycVerification::firstOrNew(['user_id' => $request->user()->id]);
        return Inertia::render('Kyc/Index', [
            'kyc' => $kyc,
            'disclaimer' => 'This is not a public offer. Please read all documents carefully before purchasing.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required','string','max:255'],
            'citizenship' => ['required','string','max:120'],
            'passport_number' => ['required','string','max:120'],
            'country_of_residence' => ['required','string','max:120'],
            'passport_photo' => ['nullable','file','mimes:jpeg,jpg,png,pdf'],
            'selfie_photo' => ['nullable','file','mimes:jpeg,jpg,png'],
        ], [
            'passport_photo.mimes' => 'Invalid passport photo format.',
        ]);

        $kyc = KycVerification::firstOrNew(['user_id' => $request->user()->id]);
        $kyc->fill($data);
        if ($request->hasFile('passport_photo')) {
            $path = $request->file('passport_photo')->store('private/kyc', 'local');
            $kyc->passport_photo_path = $path;
        }
        if ($request->hasFile('selfie_photo')) {
            $path2 = $request->file('selfie_photo')->store('private/kyc', 'local');
            $kyc->selfie_photo_path = $path2;
        }
        if (!$kyc->exists) {
            $kyc->status = 'pending';
        } else {
            $kyc->status = 'pending';
        }
        $kyc->save();

        return redirect()->route('investor.dashboard')->with('status', 'KYC submitted. Verification pending.');
    }
}
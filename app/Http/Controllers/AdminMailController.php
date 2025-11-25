<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AdminMailController extends Controller
{
    public function index()
    {
        $config = [
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
        return Inertia::render('Admin/Mail/Test', [ 'config' => $config ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to' => ['required','email'],
        ]);
        try {
            Mail::raw('SMTP connectivity test message', function ($m) use ($data) {
                $m->to($data['to'])->subject('SMTP Test');
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
        return back()->with('success', 'Test email sent');
    }
}


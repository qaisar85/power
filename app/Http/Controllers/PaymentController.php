<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShareTransaction;

class PaymentController extends Controller
{
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            \Illuminate\Support\Facades\Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Illuminate\Support\Facades\Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                /** @var \Stripe\Checkout\Session $session */
                $session = $event->data->object;
                if (($session->payment_status ?? null) === 'paid') {
                    return $this->confirmByReference($session->id);
                }
                break;
            default:
                // ignore other events
                break;
        }

        return response()->json(['received' => true]);
    }

    public function yookassaWebhook(Request $request)
    {
        $ref = $request->input('object.id');
        return $this->confirmByReference($ref);
    }

    public function cryptoWebhook(Request $request)
    {
        $ref = $request->input('txid');
        return $this->confirmByReference($ref);
    }

    protected function confirmByReference(?string $reference)
    {
        if (!$reference) return response('ok');
        $tx = ShareTransaction::where('payment_reference', $reference)->first();
        if (!$tx) return response('ok');
        if ($tx->status !== 'confirmed') {
            $tx->status = 'confirmed';
            $tx->save();
            // Update holding
            $holding = \App\Models\ShareHolding::firstOrCreate(['user_id' => $tx->user_id], ['shares' => 0]);
            $holding->shares += ($tx->type === 'buy') ? $tx->shares : -$tx->shares;
            $holding->save();

            // Generate certificate (PDF if library available, otherwise HTML fallback)
            try {
                $user = \App\Models\User::find($tx->user_id);
                $data = [
                    'user' => $user,
                    'tx' => $tx,
                    'issued_at' => now(),
                ];

                $relativePath = null;
                if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.share', $data)->setPaper('a4', 'portrait');
                    $fileName = 'certificates/certificate_tx_' . $tx->id . '.pdf';
                    $relativePath = 'private/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('local')->put($relativePath, $pdf->output());
                } else {
                    // HTML fallback if PDF library is not installed
                    $html = view('certificates.share', $data)->render();
                    $fileName = 'certificates/certificate_tx_' . $tx->id . '.html';
                    $relativePath = 'private/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('local')->put($relativePath, $html);
                }

                $tx->certificate_path = $relativePath;
                $tx->save();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Certificate generation failed', ['error' => $e->getMessage(), 'tx_id' => $tx->id]);
            }

            // Send confirmation notification to user
            try {
                $user = \App\Models\User::find($tx->user_id);
                if ($user) {
                    $user->notify(new \App\Notifications\SharePurchaseConfirmed($tx));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Share purchase notification failed', ['error' => $e->getMessage(), 'tx_id' => $tx->id]);
            }
        }
        return response('ok');
    }
}
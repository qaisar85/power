<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ShareTransaction;

class SharePurchaseConfirmed extends Notification
{
    use Queueable;

    public function __construct(public ShareTransaction $transaction)
    {
        //
    }

    public function via($notifiable): array
    {
        $channels = ['mail', 'database'];
        // Conditionally enable Twilio SMS if the channel library is installed and we have a phone
        try {
            $twilioAvailable = class_exists('NotificationChannels\\Twilio\\TwilioChannel')
                && class_exists('NotificationChannels\\Twilio\\TwilioSmsMessage');
            $to = method_exists($notifiable, 'routeNotificationForTwilio')
                ? $notifiable->routeNotificationForTwilio($this)
                : ($notifiable->phone ?? null);
            if ($twilioAvailable && $to) {
                $channels[] = 'twilio';
            }
        } catch (\Throwable $e) {
            // Ignore and keep default channels
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('investor.dashboard');
        return (new MailMessage)
            ->subject('Share Purchase Confirmed')
            ->greeting('Congratulations!')
            ->line('Your recent share purchase has been confirmed.')
            ->line('Shares: ' . $this->transaction->shares)
            ->line('Total: $' . number_format((float) $this->transaction->amount, 2))
            ->action('View Investor Dashboard', $url)
            ->line('Your Shareholder Certificate is available from your dashboard.');
    }

    // Twilio SMS message when available
    public function toTwilio($notifiable)
    {
        $content = 'Share purchase confirmed: ' . $this->transaction->shares . ' shares, Total $' .
            number_format((float) $this->transaction->amount, 2) . '. Open Investor Dashboard.';
        if (class_exists('NotificationChannels\\Twilio\\TwilioSmsMessage')) {
            return (new \NotificationChannels\Twilio\TwilioSmsMessage())
                ->content($content);
        }
        return null;
    }

    public function toArray($notifiable): array
    {
        $ctaUrl = route('investor.dashboard');
        return [
            'type' => 'share_purchase_confirmed',
            'title' => 'Share Purchase Confirmed',
            'message' => 'Your purchase of ' . $this->transaction->shares . ' shares totalling $' . number_format((float) $this->transaction->amount, 2) . ' is confirmed.',
            'cta_url' => $ctaUrl,
            'cta_label' => 'Open Investor Dashboard',
            'level' => 'success',
            'transaction_id' => $this->transaction->id,
            'shares' => $this->transaction->shares,
            'amount' => (float) $this->transaction->amount,
            'certificate_path' => $this->transaction->certificate_path,
        ];
    }
}
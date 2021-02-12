<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\Order;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomerOrderRegistered extends Notification
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable instanceof Customer) {
            if (filled(config('twilio-notification-channel.account_sid'))) {
                return [TwilioChannel::class];
            }
            Log::warning('Cannot send notification, SMS provider not properly configured.');
        }
    }

    public function toTwilio($notifiable)
    {
        $message = __("Hello :customer_name (ID :customer_id), we have received your order with ID #:id and will get back to you soon.", [
            'customer_name' => $notifiable->name,
            'customer_id' => $notifiable->id_number,
            'id' => $this->order->id,
        ]);
        $message .= "\n" . __('More information: ');
        $message .= route('order-lookup', [
            'lang' => $notifiable->locale,
        ]);
        return (new TwilioSmsMessage())
            ->content($message);
    }
}

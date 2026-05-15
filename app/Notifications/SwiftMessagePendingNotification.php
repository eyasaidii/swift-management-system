<?php

namespace App\Notifications;

use App\Models\MessageSwift;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SwiftMessagePendingNotification extends Notification
{
    use Queueable;

    public function __construct(private MessageSwift $message) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'reference'  => $this->message->REFERENCE,
            'type'       => $this->message->TYPE_MESSAGE,
            'direction'  => $this->message->DIRECTION,
            'sender'     => $this->message->SENDER_NAME ?? $this->message->SENDER_BIC,
            'amount'     => $this->message->AMOUNT,
            'currency'   => $this->message->CURRENCY,
        ];
    }
}

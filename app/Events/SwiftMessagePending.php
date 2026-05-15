<?php

namespace App\Events;

use App\Models\MessageSwift;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwiftMessagePending implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;

    public string $reference;

    public string $typeMessage;

    public string $direction;

    public string $status;

    public ?string $senderName;

    public ?string $amount;

    public ?string $currency;

    public string $createdAt;

    public function __construct(MessageSwift $message)
    {
        $this->messageId = $message->id;
        $this->reference = $message->REFERENCE ?? "#{$message->id}";
        $this->typeMessage = $message->TYPE_MESSAGE ?? '—';
        $this->direction = $message->DIRECTION ?? 'IN';
        $this->status = $message->STATUS ?? 'pending';
        $this->senderName = $message->SENDER_NAME;
        $this->amount = $message->AMOUNT !== null
            ? number_format((float) $message->AMOUNT, 2, '.', ' ')
            : null;
        $this->currency = $message->CURRENCY;
        $this->createdAt = now()->format('H:i');
    }

    /**
     * Broadcast sur le canal privé réservé aux swift-managers.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('swift-managers'),
        ];
    }

    /**
     * Nom de l'événement côté JS.
     */
    public function broadcastAs(): string
    {
        return 'swift.message.pending';
    }

    /**
     * Payload envoyé au front-end.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'reference' => $this->reference,
            'type' => $this->typeMessage,
            'direction' => $this->direction,
            'status' => $this->status,
            'sender' => $this->senderName,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'time' => $this->createdAt,
        ];
    }
}

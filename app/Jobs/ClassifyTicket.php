<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function handle(TicketClassifier $classifier): void
    {
        $result = $classifier->classify($this->ticket);

        $this->ticket->update([
            'category'     => $this->ticket->is_category_manual ? $this->ticket->category : $result['category'],
            'explanation' => $result['explanation'],
            'confidence' => $result['confidence'],
        ]);
    }
}

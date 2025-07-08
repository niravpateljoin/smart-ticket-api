<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

class BulkClassifyTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:bulk-classify-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tickets = Ticket::whereNull('explanation')->get();

        foreach ($tickets as $ticket) {
            ClassifyTicket::dispatch($ticket);
        }

        $this->info('Dispatched classify job for ' . $tickets->count() . ' tickets.');
    }
}

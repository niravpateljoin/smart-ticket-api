<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    /**
     * Store a new ticket
     */
    public function store(Request $request): JsonResponse

    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $ticket = Ticket::create($data);

        return response()->json($ticket, 201);
    }

    /**
     * List tickets with search, filter, and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query();
        
        if ($search = $request->input('search')) {
            $query->where('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $tickets = $query->orderByDesc('created_at')->paginate(10);

        return response()->json($tickets);
    }

    /**
     * Get ticket details
     */
    public function show(string $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);
        return response()->json($ticket);
    }

    /**
     * Update ticket fields: status, category, note
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate([
            'status' => 'nullable|in:open,pending,closed',
            'category' => 'nullable|string|max:50',
            'note' => 'nullable|string',
        ]);

        if (isset($data['category'])) {
            $data['is_category_manual'] = true;
        }

        $ticket->update($data);

        return response()->json($ticket);
    }

    /**
     * Dispatch classification job for the ticket
     */
    public function classify(string $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        ClassifyTicket::dispatch($ticket);

        return response()->json(['message' => 'Classification job dispatched.']);
    }

    /**
     * Get stats for dashboard
     */
    public function stats(): JsonResponse
    {
        $statusCounts = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $categoryCounts = Ticket::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'by_status' => $statusCounts,
            'by_category' => $categoryCounts,
        ]);
    }

    /**
     * Delete a ticket
    */
    public function destroy(string $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully.']);
    }

}

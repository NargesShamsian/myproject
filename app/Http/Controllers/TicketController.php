<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())->get();
        return response()->json($tickets);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    try {
        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => 0, // Open
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }

    return response()->json($ticket, 201);
}

    public function show($id)
    {
        $ticket = Ticket::where('user_id', Auth::id())->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', Auth::id())->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ticket->update($request->all());

        return response()->json($ticket);
    }

    public function destroy($id)
    {
        $ticket = Ticket::where('user_id', Auth::id())->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully.']);
    }
}

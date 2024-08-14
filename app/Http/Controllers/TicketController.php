<?php

namespace App\Http\Controllers;

use App\Models\Assignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function assign(Request $request, $ticketId)
    {
        $userIds = $request->input('user_ids');

        foreach ($userIds as $userId) {
            Assignee::create([
                'request_id' => $ticketId,
                'user_id' => $userId,
                'assigner_id' => Auth::id(),
            ]);
        }
    }
}

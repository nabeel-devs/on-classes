<?php

namespace App\Http\Controllers\api\creator;

use App\Models\User;
use App\Models\Meeting;
use Illuminate\Http\Request;
use App\Models\MeetingParticipant;
use App\Http\Controllers\Controller;
use App\Http\Resources\ParticipantResource;
use App\Http\Requests\StoreParticipantRequest;

class ParticipantController extends Controller
{
    public function store(StoreParticipantRequest $request, Meeting $meeting)
    {
        $user = User::findOrFail($request->user_id);

        // Check if the user is already a participant
        if ($meeting->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a participant'], 400);
        }

        $participant = $meeting->participants()->create([
            'user_id' => $user->id,
            'meeting_id' => $meeting->id,
        ]);

        return new ParticipantResource($participant);
    }

    // Remove a participant from a meeting
    public function destroy(Meeting $meeting, MeetingParticipant $participant)
    {
        // Ensure the participant belongs to the meeting
        if ($participant->meeting_id !== $meeting->id) {
            return response()->json(['message' => 'Participant does not belong to this meeting'], 404);
        }

        // Delete the participant
        $participant->delete();

        return response()->json(['message' => 'Participant removed successfully']);
    }

    // Get all participants for a meeting
    public function index(Meeting $meeting)
    {
        $participants = $meeting->participants->load('user');
        return ParticipantResource::collection($participants);
    }
}

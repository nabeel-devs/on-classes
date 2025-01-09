<?php

namespace App\Http\Controllers\api\creator;

use App\Models\User;
use App\Models\Meeting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;

class MeetingController extends Controller
{
    public function store(StoreMeetingRequest $request)
    {
        $meeting = Meeting::create($request->validated());

        $meeting->participants()->create([
            'user_id' => $request->user_id, // The creator is automatically added as a participant
            'meeting_id' => $meeting->id,
        ]);

        return new MeetingResource($meeting);
    }

    // Get all meetings
    public function index()
    {
        $meetings = Meeting::with(['creator', 'participants.user'])->orderBy('id', 'asc')->get();
        return MeetingResource::collection($meetings);
    }

    // Get a specific meeting
    public function show(Meeting $meeting)
    {
        return new MeetingResource($meeting);
    }

    // Update a meeting
    public function update(UpdateMeetingRequest $request, Meeting $meeting)
    {
        $meeting->update($request->validated());
        return new MeetingResource($meeting);
    }

    // Delete a meeting
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return response()->json(['message' => 'Meeting deleted successfully']);
    }

    // Get all meetings for a user
    public function userMeetings($userId)
    {
        $user = User::findOrFail($userId);
        $meetings = $user->meetings; // Assuming meetings are related to users via pivot table
        return MeetingResource::collection($meetings);
    }
}

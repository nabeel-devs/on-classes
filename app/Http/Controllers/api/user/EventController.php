<?php

namespace App\Http\Controllers\api\user;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Member;
use App\Models\CallLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'start_time' => 'required|date',
            'duration' => 'nullable|integer',
            'host_user_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::create([
            'event_id' => uniqid('event_'),
            'topic' => $request->topic,
            'start_time' => $request->start_time,
            'duration' => $request->duration,
            'host_user_id' => $request->host_user_id,
        ]);

        return response()->json(['message' => 'Event created successfully', 'event' => $event], 201);
    }

    public function addMember(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'is_host' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $member = Member::create([
            'event_id' => $event->id,
            'user_id' => $request->user_id,
            'is_host' => $request->is_host ?? false,
        ]);

        return response()->json(['message' => 'Member added successfully', 'member' => $member], 201);
    }

    public function generateToken(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = $event->event_id;
        $userId = $request->user_id;
        $role = $request->is_host ? RtcTokenBuilder::RolePublisher : RtcTokenBuilder::RoleSubscriber;
        $expirationTimeInSeconds = 3600;
        $privilegeExpiredTs = now()->addSeconds($expirationTimeInSeconds)->timestamp;

        $token = RtcTokenBuilder::buildTokenWithUid($appId, $appCertificate, $channelName, $userId, $role, $privilegeExpiredTs);

        return response()->json(['token' => $token], 200);
    }

    public function logCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required|string|unique:call_logs',
            'caller_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $callLog = CallLog::create($request->all());

        return response()->json(['message' => 'Call log created successfully', 'call_log' => $callLog], 201);
    }
}

<?php

namespace App\Http\Controllers\api\user;

use App\Models\UserLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\user\CreateUserLinkRequest;
use App\Http\Requests\user\UpdateUserLinkRequest;

class UserLinkController extends Controller
{
    public function store(CreateUserLinkRequest $request)
    {
        $user = auth()->user(); // Get the authenticated user

        $link = $user->links()->create([
            'name' => $request->input('name'),
            'url' => $request->input('url'),
        ]);

        return response()->json([
            'message' => 'Link created successfully.',
            'link' => $link,
        ], 201);
    }

    public function index()
    {
        $user = auth()->user();

        $links = $user->links;

        return response()->json([
            'links' => $links,
        ]);
    }

    public function show(UserLink $userLink)
    {
        if ($userLink->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'link' => $userLink,
        ]);
    }

    public function update(UpdateUserLinkRequest $request, UserLink $userLink)
    {
        if ($userLink->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userLink->update([
            'name' => $request->input('name'),
            'url' => $request->input('url'),
        ]);

        return response()->json([
            'message' => 'Link updated successfully.',
            'link' => $userLink,
        ]);
    }


    public function destroy(UserLink $userLink)
    {
        if ($userLink->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userLink->delete();

        return response()->json([
            'message' => 'Link deleted successfully.',
        ]);
    }
}

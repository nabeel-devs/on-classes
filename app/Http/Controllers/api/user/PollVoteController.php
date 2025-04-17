<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\PollVote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PollVoteController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'option' => 'required|string',
        ]);

        // Check if the post is a poll
        if (!$post->is_poll) {
            return response()->json([
                'message' => 'This post is not a poll.',
            ], 400);
        }

        // Check if the poll has ended
        if ($post->poll_end_at && $post->poll_end_at->isPast()) {
            return response()->json([
                'message' => 'This poll has ended.',
            ], 400);
        }

        // Check if the option is valid
        if (!in_array($request->option, $post->poll_options)) {
            return response()->json([
                'message' => 'Invalid poll option.',
            ], 400);
        }

        // Check if user has already voted
        if ($post->hasUserVoted(auth()->user())) {
            return response()->json([
                'message' => 'You have already voted in this poll.',
            ], 400);
        }

        // Create the vote
        $vote = PollVote::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'option' => $request->option,
        ]);

        // Get updated poll results
        $results = $post->getPollResults();

        return response()->json([
            'message' => 'Vote recorded successfully.',
            'results' => $results,
        ], 200);
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'option' => 'required|string',
        ]);

        // Check if the post is a poll
        if (!$post->is_poll) {
            return response()->json([
                'message' => 'This post is not a poll.',
            ], 400);
        }

        // Check if the poll has ended
        if ($post->poll_end_at && $post->poll_end_at->isPast()) {
            return response()->json([
                'message' => 'This poll has ended.',
            ], 400);
        }

        // Check if the option is valid
        if (!in_array($request->option, $post->poll_options)) {
            return response()->json([
                'message' => 'Invalid poll option.',
            ], 400);
        }

        // Get the user's existing vote
        $vote = $post->getUserVote(auth()->user());

        if (!$vote) {
            return response()->json([
                'message' => 'You have not voted in this poll yet.',
            ], 400);
        }

        // Update the vote
        $vote->update([
            'option' => $request->option,
        ]);

        // Get updated poll results
        $results = $post->getPollResults();

        return response()->json([
            'message' => 'Vote updated successfully.',
            'results' => $results,
        ], 200);
    }

    public function destroy(Post $post)
    {
        // Get the user's vote
        $vote = $post->getUserVote(auth()->user());

        if (!$vote) {
            return response()->json([
                'message' => 'You have not voted in this poll yet.',
            ], 400);
        }

        // Delete the vote
        $vote->delete();

        // Get updated poll results
        $results = $post->getPollResults();

        return response()->json([
            'message' => 'Vote removed successfully.',
            'results' => $results,
        ], 200);
    }
}

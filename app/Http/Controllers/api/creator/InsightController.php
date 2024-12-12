<?php

namespace App\Http\Controllers\api\creator;

use App\Models\PostLike;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InsightController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $period = $request->query('period', 'all');

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;

            case 'weekly':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;

            case 'monthly':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;

            default:
                $startDate = null;
                $endDate = null;
        }

        $commentsCount = PostComment::whereIn('post_id', $user->posts->pluck('id'))
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        $likesCount = PostLike::whereIn('post_id', $user->posts->pluck('id'))
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->fullName(),
            'total_posts' => $user->posts->count(),
            'total_reactions' => $likesCount,
            'total_comments' => $commentsCount,
            'period' => $period,
        ]);
    }

}

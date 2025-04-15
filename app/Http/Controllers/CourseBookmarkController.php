<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\CourseBookmark;

class CourseBookmarkController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'is_bookmarked' => 'required|boolean',
        ]);

        $bookmark = CourseBookmark::where('course_id', $course->id)
                    ->where('user_id', _user()->id)
                    ->first();

        if ($bookmark) {
            $bookmark->is_bookmarked = $request->is_bookmarked;
            $bookmark->save();
        } else {
            $bookmark = CourseBookmark::create([
                'course_id' => $course->id,
                'user_id' => _user()->id,
                'is_bookmarked' => $request->is_bookmarked,
            ]);
        }

        // Count total bookmarks for the course
        $bookmarkCount = CourseBookmark::where('course_id', $course->id)
                                    ->where('is_bookmarked', true)
                                    ->count();

        return response()->json([
            'message' => 'Course bookmark status updated successfully.',
            'data' => $bookmark,
            'bookmark_count' => $bookmarkCount,
        ], 200);
    }


    // Remove a bookmark from a course
    public function destroy(CourseBookmark $bookmark)
    {
        // Check if the authenticated user owns the bookmark
        if ($bookmark->user_id !== _user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->delete();

        return response()->json([
            'message' => 'Bookmark removed successfully.',
        ]);
    }
}

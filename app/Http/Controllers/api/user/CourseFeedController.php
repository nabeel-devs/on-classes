<?php

namespace App\Http\Controllers\api\user;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\creator\CourseResource;

class CourseFeedController extends Controller
{
    public function index()
    {
        $courses = Course::with([
            'modules.lessons',
            'user',
            'category',
            'media',
            'bookmarks' => function ($q) {
                $q->where('user_id', auth()->id())->where('is_bookmarked', true);
            },
            'orderItems.order' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])->get();

        return CourseResource::collection($courses);
    }

    public function show(Course $course)
    {
        $course->load([
            'modules.lessons',
            'user',
            'category',
            'media',
            'bookmarks' => function ($q) {
                $q->where('user_id', auth()->id())->where('is_bookmarked', true);
            },
            'orderItems.order' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ]);

        return new CourseResource($course);
    }

    public function categoryCourses($categoryId)
    {
        $courses = Course::with([
            'modules.lessons',
            'user',
            'category',
            'media',
            'bookmarks' => function ($q) {
                $q->where('user_id', auth()->id())->where('is_bookmarked', true);
            },
            'orderItems.order' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])
        ->where('category_id', $categoryId)
        ->get();

        return CourseResource::collection($courses);
    }

}

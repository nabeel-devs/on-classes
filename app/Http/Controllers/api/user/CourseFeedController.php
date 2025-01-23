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
        return CourseResource::collection(Course::with(['modules.lessons', 'user','category', 'media'])->get());
    }

    public function show(Course $course)
    {
        return new CourseResource($course->load(['modules.lessons', 'user','category', 'media']));
    }



    public function categoryCourses($categoryId)
    {
        $courses = Course::with(['modules.lessons', 'user','category', 'media'])
            ->where('category_id', $categoryId)->get();

        return CourseResource::collection($courses);
    }
}

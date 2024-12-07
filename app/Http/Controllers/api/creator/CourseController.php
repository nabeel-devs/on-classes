<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\creator\CourseResource;
use App\Http\Requests\course\CourseStoreRequest;

class CourseController extends Controller
{
    public function index()
    {
        return CourseResource::collection(Course::with(['modules', 'user'])->get());
    }

    public function store(CourseStoreRequest $request)
    {
        $course = Course::create($request->validated());
        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        return new CourseResource($course->load(['modules', 'user']));
    }

    public function update(CourseStoreRequest $request, Course $course)
    {
        $course->update($request->validated());
        return new CourseResource($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return response()->noContent();
    }
}

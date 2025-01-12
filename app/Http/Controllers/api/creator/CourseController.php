<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Course;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\creator\CourseResource;
use App\Http\Requests\course\CourseStoreRequest;

class CourseController extends Controller
{
    public function index()
    {
        return CourseResource::collection(Course::with(['modules', 'user','category', 'media'])->get());
    }

    public function store(CourseStoreRequest $request)
    {
        $courseData = $request->validated();

        $courseData = collect($courseData)->except(['thumbnail'])->toArray();

        $course = Course::create($courseData);

        if ($request->hasFile('thumbnail')) {
            $course->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        return new CourseResource($course->load(['modules', 'user']));
    }

    public function update(CourseStoreRequest $request, Course $course)
    {
        $courseData = $request->validated();

        // Exclude thumbnail from direct update
        $courseData = collect($courseData)->except(['thumbnail'])->toArray();

        // Update course fields
        $course->update($courseData);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $course->clearMediaCollection('thumbnail');  // Remove existing thumbnail
            $course->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new CourseResource($course);
    }


    public function destroy(Course $course)
    {
        $course->delete();
        return response()->noContent();
    }



}

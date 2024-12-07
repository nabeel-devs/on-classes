<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Lesson;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\creator\LessonResource;
use App\Http\Requests\course\LessonStoreRequest;

class LessonController extends Controller
{
    public function index()
    {
        return LessonResource::collection(Lesson::with(['module', 'course'])->get());
    }

    public function store(LessonStoreRequest $request)
    {
        $lessonData = $request->validated();

        $lessonData = collect($lessonData)->except(['video', 'thumbnail'])->toArray();

        $lesson = Lesson::create($lessonData);

        if ($request->hasFile('video')) {
            $lesson->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('thumbnail')) {
            $lesson->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new LessonResource($lesson);
    }

    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson->load(['module', 'course']));
    }

    public function update(LessonStoreRequest $request, Lesson $lesson)
    {
        $lessonData = $request->validated();

        $lessonData = collect($lessonData)->except(['video', 'thumbnail'])->toArray();

        $lesson->update($lessonData);

        if ($request->hasFile('video')) {
            $lesson->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('thumbnail')) {
            $lesson->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new LessonResource($lesson);
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\creator\ModuleResource;
use App\Http\Requests\course\ModuleStoreRequest;

class ModuleController extends Controller
{
    public function index()
    {
        return ModuleResource::collection(Module::with(['course', 'lessons'])->get());
    }

    public function store(ModuleStoreRequest $request)
    {

        $moduleData = $request->validated();

        $moduleData = collect($moduleData)->except(['video', 'thumbnail'])->toArray();

        $module = Module::create($moduleData);

        if ($request->hasFile('video')) {
            $module->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('thumbnail')) {
            $module->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new ModuleResource($module);
    }

    public function show(Module $module)
    {
        return new ModuleResource($module->load(['course', 'lessons']));
    }

    public function update(ModuleStoreRequest $request, Module $module)
    {
        $module->update($request->validated());
        return new ModuleResource($module);

        $moduleData = $request->validated();

        $moduleData = collect($moduleData)->except(['video', 'thumbnail'])->toArray();

        $module->update($moduleData);

        if ($request->hasFile('video')) {
            $module->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('thumbnail')) {
            $module->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        return new ModuleResource($module);
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return response()->noContent();
    }
}

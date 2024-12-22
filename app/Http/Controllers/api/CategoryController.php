<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->except('icon'));

        if ($request->hasFile('icon')) {
            $category->addMedia($request->file('icon'))->toMediaCollection('icons');
        }
        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function update(CategoryStoreRequest $request, Category $category)
    {
        $category->update($request->except('icon'));

        if ($request->hasFile('icon')) {
            $category->clearMediaCollection('icons');
            $category->addMedia($request->file('icon'))->toMediaCollection('icons');
        }

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}

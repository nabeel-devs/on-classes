<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\creator\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['user', 'category', 'media'])->get();
        return ProductResource::collection($products);
    }

    // Display a single product.
    public function show($id)
    {
        $product = Product::with(['user', 'category', 'media'])->findOrFail($id);
        return new ProductResource($product);
    }

    // Store a newly created product in the database.
    public function store(StoreProductRequest $request)
    {
        $productData = $request->validated();

        $productData = collect($productData)->except(['cover_image', 'detail_images', 'source_file'])->toArray();

        $product = Product::create($productData);

        if ($request->hasFile('cover_image')) {
            $product->addMedia($request->file('cover_image'))
                    ->toMediaCollection('cover_image');
        }

        if ($request->hasFile('detail_images')) {
            foreach ($request->file('detail_images') as $image) {
                $product->addMedia($image)
                        ->toMediaCollection('detail_images');
            }
        }


        if ($request->hasFile('source_file')) {
            $product->addMedia($request->file('source_file'))
                    ->toMediaCollection('source_file');
        }

        return new ProductResource($product);
    }

    // Update the specified product in the database.
    public function update(UpdateProductRequest $request, $id)
    {

        $productData = $request->validated();

        $productData = collect($productData)->except(['cover_image', 'detail_images', 'source_file'])->toArray();
        $product = Product::findOrFail($id);


        $product->update($productData);

        if ($request->hasFile('cover_image')) {
            $product->addMedia($request->file('cover_image'))
                    ->toMediaCollection('cover_image');
        }

        if ($request->hasFile('detail_images')) {
            foreach ($request->file('detail_images') as $image) {
                $product->addMedia($image)
                        ->toMediaCollection('detail_images');
            }
        }

        if ($request->hasFile('source_file')) {
            $product->addMedia($request->file('source_file'))
                    ->toMediaCollection('source_file');
        }

        return new ProductResource($product);
    }

    // Remove the specified product from the database.
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }
}

<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserResource;

class UserController extends Controller
{
    public function allCreators()
    {
        $allCreators = User::where('role', 'creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return UserResource::collection($allCreators);
    }

    public function topCreators()
    {
        $allCreators = User::with('products')
            ->where('role', 'creator')
            ->withCount('products') // Count the number of products
            ->havingRaw('products_count > ?', [1]) // Use havingRaw for PostgreSQL compatibility
            ->orderBy('products_count', 'desc')
            ->get();

        return UserResource::collection($allCreators);
    }


}

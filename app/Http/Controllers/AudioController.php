<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AudioController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'description' => 'required|string'
        ]);
        $audio = Audio::create($request->except('audio'));


        if ($request->hasFile('audio')) {
            $audio->addMedia($request->file('audio'))->toMediaCollection('audio');
        }

        return response()->json($audio);
    }

    public function index()
    {
        $audios = Audio::with('media')->get();

        return response()->json($audios);
    }

    public function show(Audio $audio)
    {
        $audio = Audio::with('media')->findOrFail($audio);

        return response()->json($audio);
    }
}

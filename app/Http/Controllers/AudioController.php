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
            'description' => 'required|string',
            'audio' => 'nullable|file|max:5120',
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

    public function showAudio(Audio $audio)
    {
        $audio->load('media'); // Load media relationship

        return response()->json($audio);
    }
}

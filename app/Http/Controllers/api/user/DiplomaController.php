<?php

namespace App\Http\Controllers\api\user;

use App\Models\Diploma;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DiplomaResource;

class DiplomaController extends Controller
{

    public function index()
    {
        $diplomas = Diploma::with('media')->paginate(20);

        return DiplomaResource::collection($diplomas);
    }


    public function uploadDiplomaData(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'size' => 'required|string',
            'diploma_style' => 'required|string',
            'degree_type' => 'required|string',
            'major' => 'required|string',
            'concentration' => 'nullable|string',
            'university_name' => 'required|string',
            'student_name' => 'required|string',
            'student_city' => 'required|string',
            'graduation_date' => 'required|date',
            'university_city' => 'required|string',
            'university_state' => 'required|string',
            'signature' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            'logo' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            'seal' => 'required|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Add authenticated user ID to the data
        $user = auth()->user();

        // Create a new Diploma and associate it with the authenticated user
        $diploma = Diploma::create([
            'document_type' => $request->document_type,
            'size' => $request->size,
            'diploma_style' => $request->diploma_style,
            'degree_type' => $request->degree_type,
            'major' => $request->major,
            'concentration' => $request->concentration,
            'university_name' => $request->university_name,
            'university_city' => $request->university_city,
            'university_state' => $request->university_state,
            'student_name' => $request->student_name,
            'student_city' => $request->student_city,
            'graduation_date' => $request->graduation_date,
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('signature')) {
            $diploma->addMedia($request->file('signature'))->toMediaCollection('signature');
        }
        if ($request->hasFile('logo')) {
            $diploma->addMedia($request->file('logo'))->toMediaCollection('logo');
        }
        if ($request->hasFile('seal')) {
            $diploma->addMedia($request->file('seal'))->toMediaCollection('seal');
        }

        return new DiplomaResource($diploma);
    }


    public function previewDiploma($id)
    {
        $diploma = Diploma::with('media')->findOrFail($id);

        // Build preview URL dynamically (mocked for now)
        $previewUrl = "https://example.com/diploma_preview/{$diploma->id}.png";

        return response()->json([
            'success' => true,
            'message' => 'Diploma preview generated successfully.',
            'preview_url' => $previewUrl,
            'data' => new DiplomaResource($diploma),
        ]);
    }


}

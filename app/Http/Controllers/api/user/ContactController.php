<?php

namespace App\Http\Controllers\api\user;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::all();  // Retrieve all contacts from the database
        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        // Create a new contact record
        $contact = Contact::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully.',
            'data' => $contact
        ], 201);
    }

    // Display the specified contact
    public function show(Contact $contact)
    {
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }
}

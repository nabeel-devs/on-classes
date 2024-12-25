<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-upload', function () {
    Storage::disk('s3')->put('test.txt', 'Testing connection');
    return 'File uploaded successfully!';
});

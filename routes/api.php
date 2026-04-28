<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// All API routes moved to web.php for session/auth handling
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

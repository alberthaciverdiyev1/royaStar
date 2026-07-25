<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Fallback for unauthenticated requests from auth middleware
Route::any('/login', function () {
    return apiResponse(statusCode: 401, message: __('crud.unauthenticated'));
})->name('login');

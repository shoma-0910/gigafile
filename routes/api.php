<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;

Route::post('/upload', [FileController::class, 'upload']);
Route::get('/download/{link}', [FileController::class, 'download']);



Route::get('/test', function () {
    return response()->json(['status' => 'API is working']);
});
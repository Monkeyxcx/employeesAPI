<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\employeesController;

Route::get('/employees', [employeesController::class, 'index']);

Route::get('/employees/{id}', [employeesController::class, 'show']);

Route::post('/employees', [employeesController::class, 'store']);

Route::put('/employees/{id}', [employeesController::class, 'update']);

Route::delete('/employees/{id}', [employeesController::class, 'delete']);
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

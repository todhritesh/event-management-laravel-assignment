<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {
    Route::post('/create_event',[EventController::class,'create_event']);
    Route::post('/update_event/{id?}',[EventController::class,'update_event']);
    Route::post('/invite_user',[EventUserController::class,'invite_user']);
});

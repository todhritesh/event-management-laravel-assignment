<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register_user',[UserController::class,'register_user']);
Route::post('/user_login',[UserController::class,'user_login']);
Route::middleware('auth:sanctum')->group( function () {
    Route::post('/reset_password',[UserController::class,'reset_password']);
    Route::post('/user_logout',[UserController::class,'user_logout']);
    Route::get('/show_users',[UserController::class,'show_users']);
    Route::get('/show_created_events',[UserController::class,'show_created_events']);
    Route::get('/show_invites',[UserController::class,'show_invites']);
    Route::get('/show_invites_with_sorting',[UserController::class,'show_invites_with_sorting']);
    Route::get('/show_created_events_with_sorting',[UserController::class,'show_created_events_with_sorting']);
    Route::get('/show_created_events_with_date_filter',[UserController::class,'show_created_events_with_date_filter']);
    Route::get('/show_invites_with_date_filter',[UserController::class,'show_invites_with_date_filter']);
    Route::get('/show_created_events_with_search',[UserController::class,'show_created_events_with_search']);
    Route::get('/show_invites_with_search',[UserController::class,'show_invites_with_search']);
});

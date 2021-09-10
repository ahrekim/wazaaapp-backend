<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** Public routes */
// Post contact form
Route::post('/public/contactforms', 'ContactController@postContactForm');
// Post login
Route::post('/public/login', 'AuthController@login');

// Go to invite by uuid
Route::get('/public/invitation/{uuid}', 'InviteController@show');
// Respond to invitation
Route::post('/public/invitation/{uuid}', 'InviteController@update');
// Get event ics
Route::get('/public/invitation/{uuid}/event', 'InviteController@getHappeningCalendarEvent');
// Post event photos
Route::post('/public/happening/{uuid}/photo', 'PhotosController@addPhoto');
// Get event photos
Route::get('/happening/{uuid}/photos', 'PhotosController@getPhotos');
// Get event photo
Route::get('/happening/{uuid}/photo', 'PhotosController@getPhoto');

// Auth routes
Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {
    Route::get('/me', 'AdminController@me');
    Route::patch('/password', 'AdminController@changePassword');

    // Happenings management
    Route::get('/happenings', 'AdminController@getHappenings');
    // Happening management
    Route::get('/happenings/{uuid}', 'AdminController@getHappening');
    // Save happening
    Route::post('/happenings', 'AdminController@saveHappening');
    // Delete happening
    Route::delete('/happenings/{uuid}', 'AdminController@deleteHappening');
    // Delete Photos
    Route::delete('/photos/{uuid}', 'PhotosController@deletePhoto');
});
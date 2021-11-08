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
// Get public events
Route::get('/public/events/{filter?}', 'EventsController@getPublicEvents');

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
    Route::get('/me', 'AuthController@me');
    Route::patch('/password', 'AuthController@changePassword');

    // Get my events
    Route::get('/events/my', 'AuthController@getMyEvents');
    // Get events
    Route::get('/events/{filter?}', 'AuthController@getEvents');

    // Happenings management
    Route::get('/happenings', 'AuthController@getHappenings');
    // Happening management
    Route::get('/happenings/{uuid}', 'AuthController@getHappening');
    // Save happening
    Route::post('/happenings', 'AuthController@saveHappening');
    // Save happening invite
    Route::post('/happenings/{uuid}/invite', 'AuthController@saveInvite');
    // Delete happening
    Route::delete('/happenings/{uuid}', 'AuthController@deleteHappening');
    // Get my invitations
    Route::get('/invitations', 'AuthController@getMyInvitations');
    // Delete Photos
    Route::delete('/photos/{uuid}', 'PhotosController@deletePhoto');
});
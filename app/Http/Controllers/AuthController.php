<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Auth;
use App\Happening;
use App\Invite;

class AuthController extends Controller
{
    
    /**
     * Get events
     *
     * @return \Illuminate\Http\Response
     */
    public function getEvents($filter = null)
    {
        return Happening::where(function($query){
            if(Auth::user()){
                $query->where('public', '=', true)
                ->orWhere('user_id', '=', Auth::user()->id);
            } else {
                $query->where('public', '=', true);
            }
        })
        ->whereNotNull('longitude')->whereNotNull('latitude')
        ->where(function($query) use ($filter){
            if($filter){
                // Filter based on time today
                if($filter == "today"){
                    $query->whereDate("happening_starts", "<=", Carbon::now())
                        ->whereDate("happening_ends", ">=", Carbon::now());
                }
                // Filter based on time tomorrow
                if($filter == "tomorrow"){
                    $query->whereDate("happening_starts", "<=", Carbon::now()->addDay())
                        ->whereDate("happening_ends", ">=", Carbon::now()->addDay());
                }
                // Filter based on time upcoming
                if($filter == "upcoming"){
                    $query->whereDate("happening_starts", ">", Carbon::now()->addDay());
                }
            }
        })
        ->select(['uuid', 'happening_name', 'happening_information', 'longitude', 'latitude', 'happening_starts', 'happening_ends'])->get();
    }
    /**
     * Get my events
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyEvents()
    {
        return Happening::where('user_id', '=', Auth::user()->id)
        ->whereNotNull('longitude')->whereNotNull('latitude')
        ->orderBy('happening_stars', 'DESC')
        ->select(['uuid', 'happening_name', 'happening_information', 'longitude', 'latitude', 'happening_starts', 'happening_ends'])->get();
    }
    
    /**
     * Login
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // The rules
        $rules = ['email' => 'required', 'password' => 'required'];

        // Create validator
        $validator = Validator::make($request->all(), $rules);

        // If validator passes try to log in user
        if($validator->passes())
        {
            // Find the user'
            $user = User::where('email', '=', $request->get('email'))
                ->first();

            // If found
            if($user && Hash::check($request->get('password'), $user->password))
            {
                // Login user and return ther hash
                $token = $user->createToken('auth-token')->plainTextToken;
                // Return success
                return ["token" => $token];
            }
        }
        // Error
        return response()->json(['message' => 'Could not login'], 400);
    }

        /**
     * Send contact form.
     *
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {

        if(Auth::user())
        {
            return Auth::user();
        }

        // 404
        return response()->json("not found", 404);
    }


    /**
     * Change password
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        // The rules
        $rules = ['old_pw' => 'required','new_pw' => 'required','new_pw_again' => 'required'];

        // Create validator
        $validator = Validator::make($request->all(), $rules);

        // If validator passes try to log in user
        if($validator->passes())
        {
            // Find the user'
            $user = User::where('id', '=', Auth::user()->id)
                ->first();

            // If found and match
            if($user && Hash::check($request->get('old_pw'), $user->password))
            {
                // Change password
                $user->password = Hash::make($request->get('new_pw'));
                $user->save();
                // Success!
                return response()->json("OK", 200);
            }
        }
        // Error
        return response()->json(['message' => 'Could not change password'], 400);
    }

    /**
     * Get the list of appenings
     */
    public function getHappenings(){
        // Get all happenings
        $happenings = Happening::with(['invites' => function($q){
            $q->orderBy("confirmed_attendees", "DESC");
        }])->where('user_id', '=', Auth::user()->id)->get();

        // Return them
        return $happenings;
    }

    /**
     * Get happening for edit
     */
    public function getHappening($uuid){
        // Get the happening
        $happening = Happening::with("invites")->where('user_id', '=', Auth::user()->id)->where('uuid', '=', $uuid)->first();
        // Return it
        return $happening;
    }

    /**
     * Delete happening
     */
    public function deleteHappening($uuid){
        // Get the happening
        $happening = Happening::with("invites")->where('uuid', '=', $uuid)->first();
        // If found
        if($happening){
            $happening->invites()->delete();
            $happening->delete();
            // Success
            return response()->json("ok", 200);
        }
        return response()->json("not found", 404);
    }

    /**
     * Save invite
     */
    public function saveInvite(Request $request, $uuid = null){
        $validator = Validator::make($request->all(), $rules = [
            "invitation_name" => "required",
            "invitation_information" => "max:512",
            "invitee_email" => "required|email",
            "max_attendees" => "required|min:1"
        ]);

        // If validator passes
        if($validator->passes()){

            // Find the happening with uuid, must also be created by the logged in user
            $happening = Happening::where('uuid', '=', $uuid)->where('user_id', '=', Auth::user()->id)->first();
            // Happening not found
            if(!$happening){
                // Not found
                return response()->json("happening not found", 404);
            }

            // Check for existing invite or create new
            if($request->has('uuid')){
                // Find the happening with uuid, must also be created by the logged in user
                $invite = Invite::where('uuid', '=', $request->get('uuid'))->where('happening_id', '=', $happening->id)->first();
                // Create new if not found
                if(!$invite){
                    // Not found
                    return response()->json("Invite not found", 404);
                }
            } else {
                $invite = new Invite();
                $invite->uuid = Helpers::randomStr(16);
                $invite->happening_id = $happening->id;
            }

            // Store who made the happening
            $invite->invitation_name = $request->get('invitation_name');
            $invite->max_attendees = $request->get('max_attendees');
            $invite->invitee_email = $request->get('invitee_email');

            // Check if the invitee email is existing user
            $existingUser = User::where('email', '=', $request->get('invitee_email'))->first();
            if($existingUser){
                // Attach to the user directly
                $invite->user_id = $existingUser->id;
            }

            // Save invite
            $invite->save();

            // Success
            return response()->json("Success!", 200);
        }
        else {
            // Validation errors
            return response()->json(["message" => "Error!", "errors" => $validator->errors()], 400);
        }
    }


    /**
     * Save happening
     */
    public function saveHappening(Request $request){

        $validator = Validator::make($request->all(), $rules = [
            "public" => "required|boolean",
            "happening_name" => "required|max:128",
            "happening_information" => "required|max:1024",
            "happening_starts" => "required|date_format:Y-m-d H:i",
            "happening_ends" => "required|date_format:Y-m-d H:i|after:happening_starts",
            "street_address" => "max:128|required",
            "zipcode" => "string|required",
            "city" => "max:128|required",
            // Invites
            "invites" => "array",
            "invites.*.invitation_name" => "required",
            "invites.*.invitation_information" => "max:512",
            "invites.*.max_attendees" => "required|min:1",
            "latitude" => "nullable|numeric",
            "longitude" => "nullable|numeric"
        ]);

        // If validator passes
        if($validator->passes()){
            // If has uuid
            if($request->has('uuid')){
                // Find the happening with uuid, must also be created by the logged in user
                $happening = Happening::where('uuid', '=', $request->get('uuid'))->where('user_id', '=', Auth::user()->id)->first();
                // Create new if not found
                if(!$happening){
                    // Not found
                    return response()->json("happening not found", 404);
                }
            } else {
                $happening = new Happening();
                $happening->uuid = Helpers::randomStr(16);
            }

            // Update data
            if($request->has('happening_type'))
            {
                // Store if any
                $happening->happening_type = $request->get("happening_type");
            }

            // Store if exact location set
            if($request->has('latitude'))
            {
                // Store if any
                $happening->latitude = $request->get("latitude");
            }
            if($request->has('longitude'))
            {
                // Store if any
                $happening->longitude = $request->get("longitude");
            }

            $happening->public = $request->get("public");
            $happening->happening_name = $request->get("happening_name");
            $happening->happening_name_local = $request->get("happening_name");
            $happening->happening_information = $request->get("happening_information");
            $happening->happening_starts = $request->get("happening_starts");
            $happening->happening_ends = $request->get("happening_ends");

            $happening->street_address = $request->get("street_address");
            $happening->zipcode = $request->get("zipcode");
            $happening->city = $request->get("city");
            // Store who made the happening
            $happening->user_id = Auth::user()->id;
            // Save happening
            $happening->save();

            // Success
            return response()->json("Success!", 200);

        } else {
            return response()->json(["message" => "Error!", "errors" => $validator->errors()], 400);
        }
    }

    /**
     * Get the authenticated users invitations
     * @return Invitation
     */
    public function getMyInvitations()
    {
        return Invite::with('happening')
            ->where('user_id', '=', Auth::user()->id)
            ->get();
    }

}

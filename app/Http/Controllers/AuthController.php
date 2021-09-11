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

class AuthController extends Controller
{
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
     * Save happening
     */
    public function saveHappening(Request $request){

        $validator = Validator::make($request->all(), $rules = [
            "happening_type" => "max:128",
            "happening_name" => "required|max:128",
            "happening_information" => "required|max:1024",
            "happening_starts" => "required|date_format:d.m.Y H:i",
            "happening_ends" => "required|date_format:d.m.Y H:i|after:happening_starts",
            "street_address" => "max:128|required",
            "zipcode" => "numeric|required",
            "city" => "max:128|required",
            // Invites
            "invites" => "array",
            "invites.*.invitation_name" => "required",
            "invites.*.invitation_information" => "max:512",
            "invites.*.max_attendees" => "required|min:1",
        ]);

        // If validator passes
        if($validator->passes()){
            // If has uuid
            if($request->has('uuid')){
                // Find the happening with uuid
                $happening = Happening::where('uuid', '=', $request->get('uuid'))->first();
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
            $happening->happening_name = $request->get("happening_name");
            $happening->happening_information = $request->get("happening_information");
            $happening->happening_starts = $request->get("happening_starts");
            $happening->happening_ends = $request->get("happening_ends");

            $happening->street_address = $request->get("street_address");
            $happening->zipcode = $request->get("zipcode");
            $happening->city = $request->get("city");
            // Save happening
            $happening->save();

            // Set invites to var
            $invites = $request->get("invites");
            // Store invite ids to array, init
            $inviteIds = [];
            // Check if there are any invites
            if(count($invites))
            {
                foreach($invites as $invite)
                {
                    if(isset($invite["uuid"]))
                    {
                        // Find by possible uuid
                        $modifyInvite = Invite::where('uuid', '=', $invite['uuid'])->first();
                    } else {
                        $modifyInvite = new Invite();
                        $modifyInvite->uuid = Helpers::randomStr(16);
                        $modifyInvite->happening_id = $happening->id;
                    }

                    // Modify/create values
                    $modifyInvite->invitation_name = $invite["invitation_name"];
                    if(isset($invite["invitation_information"]))
                    {
                        $modifyInvite->invitation_information = $invite["invitation_information"];
                    }
                    $modifyInvite->max_attendees = $invite["max_attendees"];
                    $modifyInvite->save();

                    // Set as safe
                    $inviteIds[] = $modifyInvite->id;
                }
            }

            // remove all invites that are not in array
            DB::table('invites')->whereNull('deleted_at')->where('happening_id', '=', $happening->id)->whereNotIn('id', $inviteIds)
                ->update([
                    "deleted_at" => Carbon::now()
                ]);

            // Success
            return response()->json("Success!", 200);

        } else {
            return response()->json(["message" => "Error!", "errors" => $validator->errors()], 400);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Gallery;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

}

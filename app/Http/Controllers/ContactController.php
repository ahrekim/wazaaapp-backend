<?php

namespace App\Http\Controllers;

use App\Gallery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Send contact form.
     *
     * @return \Illuminate\Http\Response
     */
    public function postContactForm(Request $request)
    {
        // Rules
        $rules = [
            'contact_email' => 'required|email',
            'message' => 'required|max:1024'
        ];

        // Client IP
        $clientIp = $request->ip();

        // Create validator
        $validator = Validator::make($request->all(), $rules);

        // Validate
        if($validator->passes())
        {
            // Store the contact to database
            DB::table('contact_requests')
                ->insert([
                    'response_email' => $request->get('contact_email'),
                    'message' => $request->get('message'),
                    'sender_ip' => $clientIp,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

            // Return success
            return response()->json(['message' => 'Contact form sent'], 200);
        } else {
            // Return error
            return response()->json(['message' => 'Error sending form'], 400);
        }

        // Not found
        abort(404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Gallery;
use App\Happening;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventsController extends Controller
{
    /**
     * Get public events from DB
     * Will also check if logged in and shows private events
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublicEvents($filter = null)
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
}

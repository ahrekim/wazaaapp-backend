<?php

namespace App\Http\Controllers;

use App\Gallery;
use App\Happening;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * Get public events from DB
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublicEvents($x = null, $y = null)
    {
        return Happening::where('public', '=', true)->whereNotNull('longitude')->whereNotNull('latitude')
            ->select(['uuid', 'happening_name', 'happening_information', 'longitude', 'latitude', 'happening_starts', 'happening_ends'])->get();
    }
}

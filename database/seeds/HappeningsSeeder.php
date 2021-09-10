<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Happening;
use GuzzleHttp\Client;
use App\Helpers\Helpers;
use Illuminate\Support\Carbon;

class HappeningsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new GuzzleHttp\Client();
        $musicEvents = $client->request("GET", "https://api.hel.fi/linkedevents/v1/event?keywords=yso:p1808&start=today&end=".Carbon::now()->addDays(7));

        if($musicEvents->getStatusCode()){
            $events = json_decode($musicEvents->getBody());
            //return dd($events->data[1]);
            if(count($events->data)){
                foreach($events->data as $eventData){

                    if(isset($eventData->name->fi)){
                        $happening = new Happening();
                        $happening->uuid = Helpers::randomStr(16);
                        $happening->source_identifier = $eventData->id;
                        $happening->public = true;
                        $happening->managed_happening = false;
                        $happening->happening_name = $eventData->name->fi;
                        $happening->happening_information = $eventData->short_description->fi;
                        $happening->happening_starts = Carbon::parse($eventData->start_time)->format("d.m.Y H:i");
                        $happening->happening_ends = Carbon::parse($eventData->end_time)->format("d.m.Y H:i");
    
                        // If there is a location
                        if($eventData->location){
                            $arrLink = (array)$eventData->location;
                            $arrLink = $arrLink["@id"];
                            // Get via guzzle
                            $locationClient = new GuzzleHttp\Client();
                            $eventLocation = $locationClient->request("GET", $arrLink);
                            if($eventLocation->getStatusCode() == 200){
                                $eventLocation = json_decode($eventLocation->getBody());
                                $happening->street_address = $eventLocation->street_address->fi;
                                $happening->city = $eventLocation->address_locality->fi;
                                $happening->latitude = $eventLocation->position->coordinates[0];
                                $happening->longitude = $eventLocation->position->coordinates[1];
                            }
                        }
                        $happening->save();
                    }
                }
            }
        }
    }
}

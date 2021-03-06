<?php

use Illuminate\Database\Seeder;
use App\Happening;
use App\Helpers\Helpers;
use App\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class HappeningsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "This gets events from Helsinki API, very slow. For demo purposes this can be cancelled after a suitable amount of events has been imported.";

        $processingStarted = false;
        $client = new GuzzleHttp\Client();
        $page = "https://api.hel.fi/linkedevents/v1/event?start=".Carbon::now()->format('Y-m-d')."&end=".Carbon::now()->addDays(7)->format('Y-m-d');
        while($page != null){
            $musicEvents = $client->request("GET", $page);
            if($musicEvents->getStatusCode() == 200){
                $events = json_decode($musicEvents->getBody());

                // Start progress bar
                if($processingStarted == false){
                    $output = new ConsoleOutput();
                    $progress = new ProgressBar($output, $events->meta->count);
                    $processingStarted = true;
                }

                $page = $events->meta->next;
                if(count($events->data)){
                    foreach($events->data as $eventData){
                        if(isset($eventData->name->fi)){
                            $happening = new Happening();
                            $happening->locality = "fi";
                            $happening->uuid = Helpers::randomStr(16);
                            $happening->source_identifier = $eventData->id;
                            $happening->public = true;
                            $happening->managed_happening = false;
                            $happening->happening_name = isset($eventData->name->en) ? $eventData->name->en : $eventData->name->fi;
                            $happening->happening_information = isset($eventData->short_description->en) ? $eventData->short_description->en : $eventData->short_description->fi;
                            $happening->happening_name_local = $eventData->name->fi;
                            $happening->happening_information_local = $eventData->short_description->fi;
                            $happening->happening_starts = Carbon::parse($eventData->start_time)->format("Y-m-d H:i");
                            $happening->happening_ends = Carbon::parse($eventData->end_time)->format("Y-m-d H:i");
        
                            // If there is a location
                            if($eventData->location){
                                $arrLink = (array)$eventData->location;
                                $arrLink = $arrLink["@id"];
                                // Get via guzzle
                                $locationClient = new GuzzleHttp\Client();
                                $eventLocation = $locationClient->request("GET", $arrLink);
                                if($eventLocation->getStatusCode() == 200){
                                    $eventLocation = json_decode($eventLocation->getBody());
                                    $happening->street_address = $eventLocation->street_address->fi ?? null;
                                    $happening->city = $eventLocation->address_locality->fi ?? null;
                                    if(isset($eventLocation->position)){
                                        $happening->latitude = $eventLocation->position->coordinates[1];
                                        $happening->longitude = $eventLocation->position->coordinates[0];
                                    }
                                }
                            }
                            // Save happening
                            $happening->save();
    
                            // If there are keywords
                            if(count($eventData->keywords)){
                                foreach($eventData->keywords as $keywordLink){
                                    // Get the keyword from link
                                    $keywordLink = (array)$keywordLink;
                                    $link = $keywordLink['@id'];
                                    // Get via guzzle
                                    $keywordClient = new GuzzleHttp\Client();
                                    $keyword = $keywordClient->request("GET", $link);
                                    if($keyword->getStatusCode() == 200){
                                        // get the keywords
                                        $keyword = json_decode($keyword->getBody());
                                        $keywords = (array)$keyword->name;
                                        // Save tag for each keyword with same uuid
                                        $uuid = Str::uuid();
                                        if(count($keywords)){
                                            foreach($keywords as $locale => $word){
                                                $tag = Tag::where('tag_name', '=', $word)
                                                    ->where('tag_locality', '=', $locale)->first();
    
                                                // Create new if does not exist
                                                if(!$tag){
                                                    $tag = new Tag();
                                                    $tag->uuid = $uuid;
                                                    $tag->tag_locality = $locale;
                                                    $tag->tag_name = $word;
                                                    $tag->save();
                                                }
                                                // Sync tag with happening
                                                $tag->happenings()->syncWithoutDetaching($happening);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $progress->advance();
                    }
                }
            } else {
                break;
            }
        }
        $progress->finish();
    }
}

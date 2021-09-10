<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use App\Invite;
use Illuminate\Support\Facades\Validator;
use App\Happening;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Support\Carbon;
use Eluceo\iCal\Domain\ValueObject\Location;

class InviteController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        // Get the invite
        $invite = Invite::where('uuid', '=', $uuid)->with('happening')->first();
        // If found return
        if($invite){
            return $invite;
        }

        // Not found
        return response()->json("not found", 404);
    }
    /**
     * Update the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->only(['attendee_count', 'invitation_information']), [
            "attendee_count" => "nullable|integer|min:0",
            "invitation_information" => "nullable|max:1000"
        ]);

        // If pass
        if($validator->passes())
        {
            $count = $request->get('attendee_count');
            // Get the invite
            $invite = Invite::where('uuid', '=', $uuid)->with('happening')->first();
            // If found return
            if($invite){

                if($request->has('invitation_information'))
                {
                    $invite->invitation_information = $request->get('invitation_information');
                }

                // Make sure not over max, if so do nothing
                if($count <= $invite->max_attendees)
                {
                    // Update resource
                    $invite->confirmed_attendees = $count;
                    $invite->save();
                }

                // Return resource
                return $invite;
            }

            // Not found
            return response()->json("not found", 404);
        } else {
            return response()->json(["message" => "Käytä enintään 1000 merkkiä lisätietokentässä"], 400);
        }

        // Abort
        return response()->json("Error", 400);
    }

    /**
     * Get happening calendar event
     * @param string $happeningUuid
     */
    public function getHappeningCalendarEvent($happeningUuid = null){
        // Find happening
        $happening = Happening::where('uuid', '=', $happeningUuid)
            ->where('happening_starts', '>', Carbon::now())->first();

        // If found create ical file
        if($happening){
            $start = new DateTime(Carbon::parse($happening->happening_starts), false);
            $end = new DateTime(Carbon::parse($happening->happening_ends_org), false);
            $occurrence = new TimeSpan($start, $end);
            $location = new Location($happening->street_address.", ".$happening->zipcode.", ".$happening->city);

            $event = new Event($uid = new UniqueIdentifier($happening->uuid));
            $event->setSummary($happening->happening_type." - ". $happening->happening_name );
            $event->setDescription($happening->happening_information);
            $event->setOccurrence($occurrence);
            $event->setLocation($location);

            $calendar = new Calendar([$event]);

            $componentFactory = new CalendarFactory();
            $calendarComponent = $componentFactory->createCalendar($calendar);

            return response($calendarComponent, 200)
                ->header("Content-Type", "text/calendar; charset=utf-8")
                ->header("Content-Disposition", 'attachment; filename="cal.ics"');
        }

        // Not found
        return response()->json("Not found", 404);
    }
}

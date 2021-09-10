<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use App\Invite;
use Illuminate\Support\Facades\Validator;
use App\Happening;
use App\HappeningPhoto;
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
use App\Helpers\Helpers;

class PhotosController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addPhoto(Request $request, $uuid)
    {
        // Get the invite
        $happening = Happening::where('uuid', '=', $uuid)->first();
        // If found return
        if($happening){

            // Max 10MB images
            $validator = Validator::make($request->only('photo'), ['photo' => 'max:10240|mimes:jpg,png']);

            if($validator->passes())
            {
                // Get the photo
                $photo = $request->file('photo');
                // Begin creating db entry
                $happeningPhoto = new HappeningPhoto();
                $happeningPhoto->happening_id = $happening->id;
                $happeningPhoto->uuid = Helpers::randomStr(16);
                $happeningPhoto->original_name = $photo->getClientOriginalName();
                $happeningPhoto->filename = Helpers::randomStr(32).".".$photo->getClientOriginalExtension();
                $happeningPhoto->mimetype = $photo->getClientMimeType();
                $happeningPhoto->size = $photo->getSize();

                // MOve file to location
                $photo->move(storage_path('/happening_photos'), $happeningPhoto->filename);

                // If success
                if(file_exists(storage_path('/happening_photos/'.$happeningPhoto->filename)))
                {
                    // Save
                    $happeningPhoto->save();

                    // Success
                    return response()->json("OK", 200);
                }

            } else {
                // Return error fot this image
                return response()->json(['message' => 'Image too large'], 400);
            }
        }
        // Not found error
        return response()->json(['message' => 'Not found'], 404);
    }

    /**
     * Get happening photos
     *
     * @return \Illuminate\Http\Response
     */
    public function getPhotos($uuid)
    {
        // Get the invite
        $happening = Happening::where('uuid', '=', $uuid)->first();
        // If found return
        if($happening){

            // Get happening photos
            $happeningPhotos = HappeningPhoto::where('happening_id', '=', $happening->id)->get();
            // Return result
            return $happeningPhotos;
        }
        // Not found error
        return response()->json(['message' => 'Not found'], 404);
    }

    /**
     * Get happening photo
     *
     * @return \Illuminate\Http\Response
     */
    public function getPhoto($filename = null)
    {
        // Get the invite
        $happeningPhoto = HappeningPhoto::where('filename', '=', $filename)->first();

        // If found return
        if($happeningPhoto && file_exists(storage_path('/happening_photos/'.$happeningPhoto->filename))){
            return response()->download(storage_path('/happening_photos/'.$happeningPhoto->filename));
        }
        // Not found error
        return response()->json(['message' => 'Not found'], 404);
    }

    /**
     * Delete photo
     *
     * @return \Illuminate\Http\Response
     */
    public function deletePhoto($filename = null)
    {
        // Get the invite
        $happeningPhoto = HappeningPhoto::where('filename', '=', $filename)->first();

        // If db entry found
        if($happeningPhoto)
        {
            // Delete DB entry
            $happeningPhoto->delete();
            
            // Unlink file
            if(file_exists(storage_path('/happening_photos/'.$happeningPhoto->filename))){
                unlink(storage_path('/happening_photos/'.$happeningPhoto->filename));
            }

            // Success
            return response()->json(['message' => "Kuva poistettu"], 200);
        }

        // Not found error
        return response()->json(['message' => 'Not found'], 404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    function create_event(Request $req){
        $validator = Validator::make($req->all(),[
            "event_name"=>"required",
            "event_on"=>"required|date|after_or_equal:today|date_format:Y-m-d H:i:s|",
            "event_description"=>"required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if ($validator->passes()) {
            $event = new Event();
            $event->event_name = $req->event_name;
            $event->event_on = $req->event_on;
            $event->event_description = $req->event_description;
            $event->user_id = Auth::user()->id;


            if($event->save()){
                return response()->json([
                    "msg" => "event created successfully"
                ],200);
            }
        }
        return response()->json([
            "msg" => "server error"
        ],500);
    }

    function update_event(Request $req , $id=null){
        $event = Event::where([['id',$id],['user_id',Auth::id()]])->count();
        if(!$event){
            return response()->json([
                'msg'=>'invalid attempt'
            ],400);
        }
        $validator = Validator::make($req->all(),[
            "event_name"=>"required",
            "event_on"=>"required|date|after_or_equal:today|date_format:Y-m-d H:i:s|",
            "event_description"=>"required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if ($validator->passes()) {
            $event = Event::where('id',$id)->first();
            if($event){
                $event->event_name = $req->event_name;
                $event->event_on = $req->event_on;
                $event->event_description = $req->event_description;
                $event->user_id = Auth::user()->id;

                if($event->save()){
                    return response()->json([
                        "msg" => "event updated successfully"
                    ],200);
                }
            }
        }
        return response()->json([
            "msg" => "server error"
        ],500);
    }


}

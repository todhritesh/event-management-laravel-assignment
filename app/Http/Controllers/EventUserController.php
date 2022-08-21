<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventUserController extends Controller
{
    function invite_user(Request $req){
        $validator = Validator::make($req->all(),[
            "event_id"=>"required|numeric|exists:events,id",
            "user_id"=>"required|exists:users,id",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        $event = Event::where([['id',$req->event_id],['user_id',Auth::id()]])->first();
        if(!$event || $req->user_id==Auth::id()){
            return response()->json([
                'msg' => 'invalid attemp'
            ],403);
        }
        if ($validator->passes()) {
            $event_user = new EventUser();
            $event_user->user_id = $req->user_id;
            $event_user->event_id = $req->event_id;

            if($event_user->save()){
                return response()->json([
                    "msg" => "invited successfully"
                ],200);
            }
        }
        return response()->json([
            "msg" => "server error"
        ],500);
    }
}

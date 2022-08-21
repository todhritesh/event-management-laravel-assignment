<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    function register_user(Request $req){
        $validator = Validator::make($req->all(),[
            "name"=>"required",
            "email"=>"required|unique:users,email|email",
            "phone"=>"required|unique:users,email|min:10|max:10",
            "password"=>"required|min:6",
            "confirm_password"=>"required|same:password",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if ($validator->passes()) {
            $user = new User();
            $user->name = $req->name;
            $user->email = $req->email;
            $user->password = Hash::make($req->password);
            $user->phone = $req->phone;

            if($user->save()){
                return response()->json([
                    "msg" => "registered successfully"
                ],200);
            }
            return response()->json([
                "msg" => "server error"
            ],500);
        }
    }

    function user_login(Request $req){
        $validator = Validator::make($req->all(),[
            "email"=>"required|email",
            "password"=>"required|min:6",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if (!Auth::attempt($req->only('email','password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }
        $user = User::where('email', $req['email'])->first();
        if($user){
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }
    }

    function reset_password(Request $req){
        $validator = Validator::make($req->all(),[
            "password"=>"required|min:6",
            "confirm_password"=>"required|same:password",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', Auth::user()->email)->first();
        if($user){
            $user->password = Hash::make($req->password);
            if($user->save()){
                return response()->json([
                    'msg'=>"password updated successfully"
                ],200);
            }
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function user_logout(){
        $user = Auth::user();
        $logout = $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        if($logout){
            return response()->json([
                'msg'=>"logged out successfully"
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_users(){
        $users = User::all()->except(Auth::id());
        if($users->count()){
            return response()->json([
                'msg'=>"users fetched",
                "data"=>$users
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }


    function show_invites(){
        $invites = EventUser::with(['created_by'=>function($query){
            $query->select('id','name');
        }])->with(['event_details'=>function($query){
            $query->select('id','event_name','event_on','event_description');
        }])->where('user_id',Auth::id())->paginate("10");

        if($invites->count()){
            return response()->json([
                'msg'=>"data fetched",
                "invites"=>$invites
            ],200);
        }
        if($invites->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_created_events(){
        $events = Event::with(['created_by'=>function($query){
            $query->select('id','name');
        }])->where('user_id',Auth::id())->paginate("10");

        if($events->count()){
            return response()->json([
                'msg'=>"data fetched",
                "created_events"=>$events,
            ],200);
        }
        if($events->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_invites_with_sorting(){
        $invites = DB::table('event_users')
        ->join("users","users.id","event_users.user_id")
        ->join("events","events.id","event_users.event_id")
        ->select("event_name","event_description","event_on","name as created_by")
        ->where('event_users.user_id',Auth::id())
        ->orderBy("events.event_name","asc")->paginate("10");

        if($invites->count()){
            return response()->json([
                'msg'=>"invites data fetched",
                "invites"=>$invites
            ],200);
        }
        if($invites->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }


    function show_created_events_with_sorting(){
        $events = Event::with(['created_by'=>function($query){
            $query->select('id','name');
        }])->where('user_id',Auth::id())->orderBy("events.event_name","asc")->paginate("10");

        if($events->count()){
            return response()->json([
                'msg'=>"data fetched",
                "created_events"=>$events,
            ],200);
        }
        if($events->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_created_events_with_date_filter(Request $req){
        $validator = Validator::make($req->all(),[
            'start'=>"required|after_or_equal:today|date_format:Y-m-d",
            'end'=>"required|after_or_equal:today|date_format:Y-m-d"
        ]);
        if($validator->fails()){
            return response()->json([
                'msg'=>'validation error',
                'errors'=>$validator->errors()
            ],400);
        }
        $events = Event::with(['created_by'=>function($query){
            $query->select('id','name');
        }])->where('user_id',Auth::id())
        ->whereDate('event_on','>=',date('Y-m-d',strtotime($req->start)))
        ->whereDate('event_on','<=',date('Y-m-d',strtotime($req->end)))
        ->paginate("10");

        if($events->count()){
            return response()->json([
                'msg'=>"data fetched",
                "created_events"=>$events,
            ],200);
        }
        if($events->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_invites_with_date_filter(Request $req){
        $validator = Validator::make($req->all(),[
            'start'=>"required|after_or_equal:today|date_format:Y-m-d",
            'end'=>"required|after_or_equal:today|date_format:Y-m-d"
        ]);
        if($validator->fails()){
            return response()->json([
                'msg'=>'validation error',
                'errors'=>$validator->errors()
            ],400);
        }
        $invites = DB::table('event_users')
        ->join("users","users.id","event_users.user_id")
        ->join("events","events.id","event_users.event_id")
        ->select("event_name","event_description","event_on","name as created_by")
        ->where('event_users.user_id',Auth::id())
        ->whereDate('events.event_on','>=',date('Y-m-d',strtotime($req->start)))
        ->whereDate('events.event_on','<=',date('Y-m-d',strtotime($req->end)))
        ->paginate("10");

        if($invites->count()){
            return response()->json([
                'msg'=>"invites data fetched",
                "invites"=>$invites
            ],200);
        }
        if($invites->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_created_events_with_search(Request $req){
        $validator = Validator::make($req->all(),[
            'search'=>"required",
        ]);
        if($validator->fails()){
            return response()->json([
                'msg'=>'validation error',
                'errors'=>$validator->errors()
            ],400);
        }
        $events = Event::with(['created_by'=>function($query){
            $query->select('id','name');
        }])->where([['user_id',Auth::id()],['event_name',$req->search]])
        ->paginate("10");

        if($events->count()){
            return response()->json([
                'msg'=>"data fetched",
                "created_events"=>$events,
            ],200);
        }
        if($events->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }

    function show_invites_with_search(Request $req){
        $validator = Validator::make($req->all(),[
            'search'=>"required",
        ]);
        if($validator->fails()){
            return response()->json([
                'msg'=>'validation error',
                'errors'=>$validator->errors()
            ],400);
        }
        $invites = DB::table('event_users')
        ->join("users","users.id","event_users.user_id")
        ->join("events","events.id","event_users.event_id")
        ->select("event_name","event_description","event_on","name as created_by")
        ->where([['event_users.user_id',Auth::id()],['events.event_name',$req->search]])
        ->paginate("10");

        if($invites->count()){
            return response()->json([
                'msg'=>"invites data fetched",
                "invites"=>$invites
            ],200);
        }
        if($invites->count()===0){
            return response()->json([
                'msg'=>"no invites",
            ],200);
        }
        return response()->json([
            'msg'=>"server error"
        ],500);
    }
}

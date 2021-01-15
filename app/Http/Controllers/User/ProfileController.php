<?php

namespace App\Http\Controllers\User;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('jwt.auth', ['except' => ['reset']]);
        $this->middleware('guest', ['only' => ['reset']]);
    }

    public function my(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }

    public function index()
    {
        $users = \App\User::all();
        return response()->json($users);
    }

    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->toArray();
        $data['options'] = json_decode($data['options'], true);

        $file = $request->file('avatar');

        if ($file) {
            $file_name = 'avatars/avatar-' . $user->id . '.' . $file->getClientOriginalExtension();
            Storage::put(
                $file_name,
                file_get_contents($file->getRealPath())
            );
            $data['avatar'] = $file_name;
        } else {
            unset($data['avatar']);
        }

        $user->update($data);

        return response()->json(['success' => true], 200);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false], 500);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user instanceof User) $user->resetPassword();
            return response()->json(['success' => true], 200);
        }
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\User;
use App\UserGroup;
use Auth;
use Illuminate\Http\Request;

class GroupsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('group.update',
            ['only' => [
                'edit',
                'update',
                'destroy'
            ]]);
        $this->middleware('group.create',
            ['only' => [
                'store',
                'create'
            ]]);
        $this->middleware('group.show',
            ['only' => [
                'show'
            ]]);
    }

    public function store(Request $request)
    {
        $data = $request->toArray();
        $users = collect($data['users']);
        $participants = $users->where('checked', true)->map(function ($item) {
            return User::find($item['id']);
        });
        $author = $request->user();
        $user_group = new UserGroup();
        $user_group->name = $data['title'];
        $user_group->author()->associate($author);
        $user_group->save();
        $id = $user_group->id;
        $user_group->users()->saveMany($participants);
        return response()->json([
            'id' => $id,
            'success' => true
        ]);
    }

    public function show($id, Request $request)
    {
        $group = UserGroup::with('users', 'author')
            ->findOrFail($id);
        $group['users'] = $group->users->map(function (User $user) {
            $user->workflow_info = array_merge($user->userBalance('array_all'), $user->userWorkflow('array_all'));
            return $user;
        });
        return response()->json($group);
    }

    public function create(Request $request)
    {
        $users = \App\User::all();
        return response()->json($users);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isType('main')) {
            $user_groups = UserGroup::all();
        } elseif ($user->isType('manager')) {
            $user_groups = UserGroup::where('author_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->get();
        } else {
            $user_groups = UserGroup::whereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->get();
        }
        return response()->json($user_groups);
    }

    public function edit($id, Request $request)
    {
        $group = UserGroup::findOrFail($id);
        $data = $group;
        $data->users = $data->users()->get()->map(function ($user) {
            $user->checked = true;
            return $user;
        });
        $data->users = User::all()->merge($data->users);
        return response()->json($data);
    }

    public function update($id, Request $request)
    {
        $data = $request->toArray();
        $users = collect($data['users']);
        $participants = $users->where('checked', true)->map(function ($item) {
            return User::find($item['id']);
        });
        $user_group = UserGroup::findOrFail($id);
        $user_group->name = $data['title'];
        $user_group->users()->detach();
        $user_group->save();
        $id = $user_group->id;
        $user_group->users()->saveMany($participants);
        return response()->json([
            'id' => $id,
            'success' => true
        ]);
    }
}

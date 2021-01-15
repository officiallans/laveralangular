<?php

namespace App\Http\Controllers;

use App\Events\WorkflowEvent;
use App\Exceptions\NoChangesException;
use App\Exceptions\WorkflowValidationException;
use App\Http\Requests;
use App\UserGroup;
use App\Workflow;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\WorkflowRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use \App\Exceptions\AccessDeniedException;
use Validator;

class WorkflowController extends Controller
{
    public function info()
    {
        return response()->json(Auth::user()->userBalance('array_all'));
    }

    public function destroy($id)
    {
        $event = Workflow::findOrFail($id);
        try {
            $event->delete();
        } catch (AccessDeniedException $e) {
            return response()->json(['success' => false], 403);
        }
        return response()->json(['success' => true], 200);
    }

    public function update(WorkflowRequest $request, $id)
    {
        $event = Workflow::findOrFail($id);
        try {
            $event->update($request->toArray());
        } catch (NoChangesException $e) {
            return response()->json($event, 304);
        } catch (AccessDeniedException $e) {
            return response()->json($event, 403);
        } catch (WorkflowValidationException $e) {
            return response()->json(['start_at' => [trans('workflow.' . $e->getMessage())]], 422);
        }

        return response()->json($event, 200);
    }

    public function index(Request $request)
    {
        $request_data = $request->toArray();
        $date = $request_data['date'];

        $start = new \DateTime($date);
        $end = clone($start);
        $interval = new \DateInterval('P1M');
        $end->add($interval);

        $data = Workflow::where('start_at', '>=', $start->format('Y-m-01'));

        if (!(isset($request_data['all']) && $request_data['all'] && $request->user()->isType('main'))) {
            $data = $data->where('start_at', '<', $end->format('Y-m-01'))->where('author_id', $request->user()->id);
        }

        $data = $data->with('author')->get()
            ->map(function (Workflow $forkflow) {
                $forkflow['hasAccess'] = true;
                try {
                    $forkflow->checkAccess();
                } catch (AccessDeniedException $e) {
                    $forkflow['hasAccess'] = false;
                }
                return $forkflow;
            });

        return response()->json($data);
    }

    public function group($id, Request $request)
    {
        $request_data = $request->toArray();
        $date = $request_data['date'];
        $start = new \DateTime($date);
        $end = clone($start);
        $interval = new \DateInterval('P1M');
        $end->add($interval);
        if ($id === 'all') {
            $groups = $request->user()->groups()->get();
            $groups->map(function ($group) use ($request) {
                try {
                    $group->checkAccess($request->user());
                    return $group;
                } catch (AccessDeniedException $e) {
                    return false;
                }
            });
        } else {
            $groups = UserGroup::findOrFail($id);
            $groups->checkAccess($request->user());
            $groups = [$groups];
        }
        $ids = collect($groups)
            ->map(function ($group) {
                return $group->users()->get()->merge($group->author()->get());
            })
            ->collapse()
            ->map(function ($user) {
                return $user->id;
            })
            ->unique()
            ->toArray();

        $data = Workflow::where('start_at', '>=', $start->format('Y-m-01'))
            ->whereIn('author_id', $ids)->with('author');
        if($id === 'all') {
            $data = $data->orWhere(function ($query) use ($start) {
                $query->where('start_at', $start->format('Y-m-d'))->where('type', 'working_off');
            });
        }
        if (!(isset($request_data['all']) && $request_data['all'] && $request->user()->isType(['main', 'manager']))) {
            $data = $data->where('start_at', '<', $end->format('Y-m-01'));
        }
        $data = $data->get()->map(function (Workflow $workflow) {
            $workflow['hasAccess'] = true;
            try {
                $workflow->checkAccess();
            } catch (AccessDeniedException $e) {
                $workflow['hasAccess'] = false;
            }
            return $workflow;
        });
        return response()->json($data);
    }

    public function store(WorkflowRequest $request)
    {
        $event = new Workflow($request->toArray());
        try {
            $event->save();
        } catch (AccessDeniedException $e) {
            return response()->json(['success' => false], 403);
        } catch (WorkflowValidationException $e) {
            return response()->json(['start_at' => [trans('workflow.' . $e->getMessage())]], 422);
        }
        return response()->json(['success' => true], 200);
    }
}

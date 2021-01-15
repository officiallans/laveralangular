<?php

namespace App\Http\Controllers;

use App\Report;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{

    public function store(Request $request)
    {
        $reports = $request->reports;
        try {
            foreach ($reports as $reportData) {
                if (isset($reportData['id'])) {
                    Report::findOrFail($reportData['id'])->update($reportData);
                } else {
                    Report::create($reportData);
                }

            }
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            get_class($e);
            $errorCode = dechex(rand());
            Log::error($errorCode . ' - ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['success' => false], 500);
        }
    }

    public function create(Request $request)
    {
        $reports = Report::byAuthor()->type(['planned', 'in_progress'])
            ->orWhere(function ($query) {
                $query->where('updated_at', '>=', date('Y-m-d'))
                    ->byAuthor();
            })
            ->get()->toArray();
        return response()->json($reports, 200);
    }

    public function index(Request $request)
    {
        $reports = Report::byAuthor()->type($request->type)->where('name', 'LIKE', '%' . $request->search . '%')->orderBy('date', 'desc')->paginate();
        return response()->json($reports, 200);
    }

    public function destroy($id, Request $request)
    {
        try {
            Report::findOrFail($id)->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function user($id, Request $request)
    {
        $typeTranslate = Report::$typeTranslate;
        $typeTranslateKeys = array_keys($typeTranslate);
        $date = $request->input('date', date('Y-m-d'));

        if ($id === 'my') {
            $id = Auth::user()->id;
            $user = Auth::user()->toArray();
        } else {
            $user = User::findOrFail($id)->toArray();
        }
        if ($date === date('Y-m-d')) {
            $reports = collect(Report::byAuthor($id)->where(function ($query) use ($date) {
                $query
                    ->where('updated_at', '>=', $date)
                    ->orWhere('date', '>=', $date);
            })->orderBy('date')->get());

            $planned = collect(Report::byAuthor($id)->where(function ($query) use ($date) {
                $query
                    ->where('updated_at', '>=', $date)
                    ->orWhere('date', '>=', $date);
            })->orderBy('date')->whereNotIn('type', ['planned'])->get()->map(function ($report) {
                /* @var $report Report */
                return $report->planned();
            }))->filter();
            $reports = $reports->merge($planned)->groupBy('date');
        } else {
            $reports = collect(
                Report::byAuthor($id)
                    ->whereBetween('created_at', [$date, date('Y-m-d', strtotime($date . ' +1 Day'))])
                    ->withoutGlobalScope('reported')->where('reported', 1)
                    ->orderBy('date')
                    ->get()
            )->groupBy('date');
        }


        if (!$reports->has($date)) {
            $reports = $reports->put($date, collect([]));
        }

        if (!$reports->contains(function ($key, $value) use ($date) {
            return $key > $date;
        })
        ) {
            $reports = $reports->put(date('Y-m-d', strtotime($date . ' +1 Weekday')), collect([]));
        }

        $reports = $reports
            ->sortBy(function ($reports, $date_i) {
                return strtotime($date_i);
            })
            ->map(function ($reports_by_date, $date_i) use ($typeTranslateKeys, $date) {
                $reports_by_type = $reports_by_date
                    ->groupBy('type');
                if (!$reports_by_type->has('planned')) {
                    $reports_by_type->put('planned', collect([]));
                }
                if (!$reports_by_type->has('solved') && $date_i === $date) {
                    $reports_by_type->put('solved', collect([]));
                }
                return $reports_by_type
                    ->sortBy(function ($reports, $type) use ($typeTranslateKeys) {
                        return array_search($type, $typeTranslateKeys);
                    });
            })->toArray();

        $data = compact('reports', 'user');
        return response()->json($data);
    }
}

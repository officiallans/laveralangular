<?php

namespace App\Console\Commands\Report;

use App\Report;
use App\User;
use App\UserGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class Info extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:info {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send info about today reports';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $typeTranslate = Report::$typeTranslate;
        $typeTranslateKeys = array_keys($typeTranslate);
        $groups = UserGroup::with('users', 'author')->get();
        $email = $this->argument('email');


        foreach ($groups as $group) {
            $users = collect($group->users->toArray())->map(function ($user) use ($typeTranslateKeys) {
                $reports = collect(Report::byAuthor($user['id'])->where(function ($query) {
                    $query
                        ->where('updated_at', '>=', date('Y-m-d'))
                        ->orWhere('date', '>=', date('Y-m-d'));
                })->orderBy('date')->get());

                $planned = collect(Report::byAuthor($user['id'])->where(function ($query) {
                    $query
                        ->where('updated_at', '>=', date('Y-m-d'))
                        ->orWhere('date', '>=', date('Y-m-d'));
                })->orderBy('date')->whereNotIn('type', ['planned'])->get()->map(function ($report) {
                    /* @var $report Report */
                    return $report->planned();
                }))->filter();

                $reports = $reports
                    ->merge($planned)->groupBy('date');

                if (!$reports->has(date('Y-m-d'))
                ) {
                    $reports = $reports->put(date('Y-m-d'), collect([]));
                }

                if (!$reports->contains(function ($key, $value) {
                    return $key > date('Y-m-d');
                })
                ) {
                    $reports = $reports->put(date('Y-m-d', strtotime(date('Y-m-d') . ' +1 Weekday')), collect([]));
                }
                $user['latest_reports'] = $reports
                    ->sortBy(function ($reports, $date) {
                        return strtotime($date);
                    })
                    ->map(function ($reports_by_date, $date) use ($typeTranslateKeys) {
                        $reports_by_type = $reports_by_date
                            ->map(function ($report) {
                                $report->reported = true;
                                return $report;
                            })
                            ->groupBy('type');
                        if (!$reports_by_type->has('planned')) {
                            $reports_by_type->put('planned', collect([]));
                        }
                        if (!$reports_by_type->has('solved') && $date === date('Y-m-d')) {
                            $reports_by_type->put('solved', collect([]));
                        }
                        return $reports_by_type
                            ->sortBy(function ($reports, $type) use ($typeTranslateKeys) {
                                return array_search($type, $typeTranslateKeys);
                            });
                    })->toArray();

                return $user;

            })->toArray();
            $name = $group->name;
            $subject = 'Звіт за ' . date('d.m.Y') . ' від групи ' . $name;
            $manager = $group->author->toArray();

            Mail::send('emails.reports.info', compact('users', 'name', 'typeTranslate'), function ($message) use ($manager, $subject, $email) {
                $message->subject($subject)->to($manager['email'], $manager['name']);
                if ($email) {
                    $message->cc($email, $email);
                }
            });
        }

    }
}

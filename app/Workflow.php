<?php

namespace App;

use App\Exceptions\NoChangesException;
use App\Exceptions\WorkflowValidationException;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use App\Events\WorkflowEvent;
use App\Exceptions\AccessDeniedException;
use Illuminate\Database\Eloquent\SoftDeletes;


class Workflow extends BaseModel
{
    use SoftDeletes;

    protected $table = 'workflow';

    const MAX_VACATION_DURATION = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['start_at', 'end_at', 'type', 'comment', 'duration', 'confirmed'];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    public static $typeTranslate = array(
        'working_off' => 'Відпрацювання',
        'time_off' => 'Відгул',
        'vacation' => 'Відпустка',
        'sick_leave' => 'Лікарняний'
    );

    public static $needConfirm = ['working_off'];
    public static $periodTypes = ['vacation', 'sick_leave'];

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id')->withoutGlobalScope('active');
    }

    public function update(array $attributes = [], array $options = [])
    {
        $this->checkAccess();
        if (!count(array_diff_assoc($attributes, $this->original))) {
            throw new NoChangesException;
        } else {
            return parent::update($attributes, $options);
        }
    }

    private function vacationSaveValidation()
    {
        $start_at = new \DateTime($this->attributes['start_at']);
        if(isset($this->attributes['end_at'])) $end_at = new \DateTime($this->attributes['end_at']);
        $duration = $this->duration;
        $author = $this->author_id;
        $created = Carbon::createFromFormat('Y-m-d H:i:s', Auth::user()->created_at);
        $now = Carbon::now();
        $work_month = $now->diffInMonths($created);

        if ($work_month < 6) {
            throw new WorkflowValidationException('error_limit_vacation', WorkflowValidationException::TOO_MANY_VACATION_DAYS);
        }
        if (isset($end_at) && $start_at->format('Y') !== $end_at->format('Y')) {
            throw new WorkflowValidationException('error_diff_years_vacation', WorkflowValidationException::DIFFERENT_YEARS_VACATION);
        }

        $workflow = Workflow::byAuthor($author)->byYear($start_at->format('Y'))->type('vacation');

        if ($this->exists) {
            $workflow->where('id', '!=', $this->id);
        }

        $total = $workflow->get()->sum(function($value) {
                return $value['duration'];
            });
        $duration += $total;

        $work_years = $now->diffInYears($created);

        $max_vacation_days = ($work_years >= 2) ? self::MAX_VACATION_DURATION + ($work_years-1) : self::MAX_VACATION_DURATION;

        if ($duration > $max_vacation_days) {
            throw new WorkflowValidationException('error_limit_vacation', WorkflowValidationException::TOO_MANY_VACATION_DAYS);
        }
    }

    private function conflictSaveValidation()
    {
        $start_at = new \DateTime($this->attributes['start_at']);
        if(isset($this->attributes['end_at'])) $end_at = new \DateTime($this->attributes['end_at']);
        $author = $this->author_id;

        $workflowFilters = collect([]);

        $workflowFilters->push(
            Workflow::byAuthor($author)->where('start_at', '<', $start_at->format('Y-m-d'))->where('end_at', '>=', $start_at->format('Y-m-d'))
        );

        if (in_array($this->attributes['type'], self::$periodTypes)) {
            if(!isset($end_at)) $end_at = new \DateTime($start_at->format('Y-m-d') . '+1 Day');

            $workflowFilters->push(
                Workflow::byAuthor($author)->whereBetween('start_at', [$start_at->format('Y-m-d'), $end_at->format('Y-m-d')])
            );
        }

        if ($this->exists) {
            $workflowFilters->map(function ($workflow) {
                $workflow->where('id', '!=', $this->id);
            });
        }

        $workflowFilters->each(function ($workflow) {
            if ($workflow->count()) {
                throw new WorkflowValidationException('error_period_conflict', WorkflowValidationException::PERIOD_CONFLICT);
            }
        });
    }

    private function confirmSaveProcessor()
    {
        // workflow type do not need confirm
        $not_need_confirm = !in_array($this->type, self::$needConfirm);

        // confirm today by user
        $today_confirm = $this->start_at === date('Y-m-d');

        // manager confirm
        $confirm_by_manager = !Auth::user()->isType('participant');

        if ($this->exists) {
            if ($this->attributes['confirmed'] && !$this->original['confirmed']) {
                $this->confirmed = $today_confirm || $confirm_by_manager;
            } else {
                // workflow already confirmed and date was not changed
                $already_confirmed = $this->confirmed && $this->original['start_at'] === $this->attributes['start_at'];

                $this->confirmed = $not_need_confirm || $already_confirmed;
            }
        } else {
            $this->confirmed = $not_need_confirm || $today_confirm;
        }
    }

    private function mailSaveEvent()
    {
        if ($this->exists) {
            $mail_event = new WorkflowEvent(clone $this);
            // clone `cause after save original data will be rewritten
        } else {
            $mail_event = new WorkflowEvent($this);
        }
        return $mail_event;
    }

    public function save(array $options = [])
    {
        if ($this->exists) {
            $this->checkAccess();
        }

        if ($this->attributes['type'] === 'vacation') {
            $this->vacationSaveValidation();
        }

        if (!in_array($this->attributes['type'], self::$periodTypes)) {
            $this->end_at = null;
        }

        $this->conflictSaveValidation();

        $this->confirmSaveProcessor();

        $mail_event = $this->mailSaveEvent();

        if (parent::save($options)) {
            Event::fire($mail_event);
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        $this->checkAccess();
        if (parent::delete()) {
            Event::fire(new WorkflowEvent($this));
            return true;
        } else {
            return false;
        }
    }


    /**
     * Check if authorized user can change this workflow.
     * User can access when:
     *  It is own workflow of user
     *  User is manager of workflow author
     *  User is main
     *
     * @return bool
     * @throws AccessDeniedException
     */
    public function checkAccess()
    {
        $user = Auth::user();
        switch ($user->type) {
            case 'main': {
                return true;
                break;
            }
            case 'manager': {
                $allowed = collect($user->groups()->get())->map(function ($group) {
                    return $group->users()->get();
                })->collapse()->push($user)->map(function ($user) {
                    return $user->id;
                });
                if ($allowed->contains($this->author_id)) return true;
                break;
            }
            default: {
                if ($this->author_id === $user->id) return true;
            }
        }
        throw new AccessDeniedException();
    }

    public function scopeConfirmed($query, $type = true)
    {
        return $query->where('confirmed', $type);
    }

    public function scopeType($query, $type)
    {
        if ($type !== 'all') {
            if (!is_array($type)) $type = [$type];
            $query->whereIn('type', $type);
        }
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('start_at', '>=', $year . '-01-01')->where('end_at', '<=', $year . '-12-31');
    }

    public function getDurationAttribute($value)
    {
        if (!in_array($this->attributes['type'], ['vacation', 'sick_leave'])) return $value;
        if (!isset($this->attributes['end_at'])) return 1;
        $start_at = new \DateTime($this->attributes['start_at']);
        $start_at = Carbon::createFromDate($start_at->format('Y'), $start_at->format('m'), $start_at->format('d'));
        $end_at = new \DateTime($this->attributes['end_at'] . '+1 day');
        $end_at = Carbon::createFromDate($end_at->format('Y'), $end_at->format('m'), $end_at->format('d'));

        $date_diff = $start_at->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end_at);

        return $date_diff;
    }

    /**
     * Virtual field, duration with type
     *
     * @return string
     */
    public function getSmartDurationAttribute()
    {
        if (in_array($this->attributes['type'], ['vacation', 'sick_leave'])) {
            return round($this->duration, 2) . ' ' . trans('workflow.days');
        } else {
            return round($this->duration / 60, 2). ' ' . trans('workflow.hours');
        }
    }
}

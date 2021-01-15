<?php

namespace App;

use App\Events\ResetPasswordEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Storage;

class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'name', 'password', 'options', 'avatar'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', '=', 1);
        });
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'options' => 'array',
    ];

    public function groups()
    {
        if ($this->type === 'participant') {
            return $this->belongsToMany('App\UserGroup', 'user_in_group', 'user_id', 'group_id');
        } else {
            return $this->hasOne('App\UserGroup', 'author_id');
        }
    }

    public function isType($name)
    {
        if (is_array($name)) {
            return in_array($this->type, $name);
        } else {
            return $this->type === $name;
        }
    }

    public function userBalance($returnType = 'balance')
    {
        $start = new \DateTime();
        $working_off = Workflow::where('type', 'working_off')
                ->where('confirmed', 1)
                ->where('author_id', $this->id)
                ->where('start_at', '<', $start->format('Y-m-d'))
                ->sum('duration') / 60;
        $time_off = Workflow::where('type', 'time_off')
                ->where('confirmed', 1)
                ->where('author_id', $this->id)
                ->where('start_at', '<', $start->format('Y-m-d'))
                ->sum('duration') / 60;
        $balance = $working_off - $time_off;
        $data = compact('working_off', 'time_off', 'balance');
        $data = array_map(function ($val) {
            $val = round($val, 2);
            $val .= ' ' . trans('workflow.hours');
            return $val;
        }, $data);
        if ($returnType === 'array_all') {
            return $data;
        } else {
            return $data[$returnType];
        }
    }

    public function userWorkflow($returnType = null, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $start = new \DateTime($year . '-01-01');
        $end = new \DateTime($year . '-12-31');
        $sick_leave = Workflow::byAuthor($this)
            ->where('type', 'sick_leave')
            ->where('start_at', '>=', $start->format('Y-m-d'))
            ->where(function ($query) use ($end) {
                $query->where('end_at', '<=', $end->format('Y-m-d'))->orWhere('end_at', '=', NULL);
            })
            ->get()
            ->sum('duration');
        $vacation = Workflow::byAuthor($this)
            ->where('type', 'vacation')
            ->where('start_at', '>=', $start->format('Y-m-d'))
            ->where(function ($query) use ($end) {
                $query->where('end_at', '<=', $end->format('Y-m-d'))->orWhere('end_at', '=', NULL);
            })
            ->get()
            ->sum('duration');

        $data = compact('vacation', 'sick_leave');
        $data = array_map(function ($val) {
            $val = round($val, 2);
            $val .= ' ' . trans('workflow.days');
            return $val;
        }, $data);
        if ($returnType === 'array_all') {
            return $data;
        } else {
            return $data[$returnType];
        }
    }

    public function setPasswordAttribute($value)
    {
        if (strlen($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function resetPassword()
    {

        $new_password = str_random(12);

        $this->password = $new_password;

        if ($this->save()) {
            Event::fire(new ResetPasswordEvent($this, $new_password));
        }
    }

    public function scopeOfType($query, $type)
    {
        $query->where('type', $type);
    }

    public function scopeNotOfType($query, $type)
    {
        $query->where('type', '!=', $type);
    }

    public function reports()
    {
        return $this->hasMany('App\Report', 'author_id', 'id');
    }

    public function workflow()
    {
        return $this->hasMany('App\Workflow', 'author_id', 'id');
    }

    public function latestReports()
    {
        return $this->reports()->where('updated_at', '>', date('Y-m-d'))->orderBy('date');
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) return $value;
        return url(Storage::url($value));
    }
}

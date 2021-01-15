<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;


class Report extends BaseModel
{
    use SoftDeletes;

    protected $table = 'reports';

    protected $fillable = ['name', 'comment', 'type', 'date'];

    public static $typeTranslate = array(
        'planned' => 'Заплановано',
        'solved' => 'Зроблено',
        'closed' => 'Не зроблено',
        'in_progress' => 'В процесі'
    );

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('archived', function (Builder $builder) {
            $builder->where('archived', '!=', 1);
        });
        static::addGlobalScope('reported', function (Builder $builder) {
            $builder->where('reported', '!=', 1);
        });
    }

    public function update(array $attributes = [], array $options = [])
    {
        if ($attributes['type'] !== $this->original['type']) {
            $old = new self($this->attributes);
            $old->setCreatedAt($this->{static::CREATED_AT});
            $old->setUpdatedAt($this->{static::UPDATED_AT});
            if ($this->revision_id) {
                $old->revision_id = $this->revision_id;
            } else {
                $old->revision_id = $this->id;
            }
            $old->archived = true;
            $old->timestamps = false;
            $old->save();
            $this->revision_id = $old->id;
        }
        return parent::update($attributes, $options);
    }

    public function scopeType($query, $type)
    {
        if ($type !== 'all') {
            if (!is_array($type)) $type = [$type];
            $query->whereIn('type', $type);
        }
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id', 'id')->withoutGlobalScope('active');
    }

    public function revision()
    {
        return $this->hasOne('App\Report', 'id', 'revision_id')->withoutGlobalScope('archived');
    }

    /**
     * Get planned report from history changes
     *
     * @return static|null
     */
    public function planned()
    {
        if ($this->revision === null) return null;
        $revision = $this->revision;
        if ($revision->type === 'planned') return $revision;
        return null;
    }


    public function setReportedAttribute($value)
    {
        static $started = false;
        if ($value && !$started) {
            $date = date('Y-m-d');

            $already_reported = self::withoutGlobalScope('reported')
                ->where('reported', 1)
                ->where('revision_id', $this->id)
                ->whereBetween('created_at', [$date, date('Y-m-d', strtotime($date . ' +1 Day'))])->count();
            if ($already_reported > 0) return;

            $started = true;
            $reported = new self($this->attributes);
            $reported->reported = true;
            $reported->revision_id = $this->id;
            $reported->author_id = $this->author_id;
            $reported->save();
        } else {
            $started = false;
            $this->attributes['reported'] = $value;
        }
    }
}

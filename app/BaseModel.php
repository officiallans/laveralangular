<?php
namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public function __construct(array $attributes = array())
    {
        if (Auth::user()) {
            $this->setRawAttributes(array(
                'author_id' => Auth::user()->id
            ), true);
        }
        parent::__construct($attributes);
    }

    public function scopeByAuthor($query, $author = null)
    {
        if ($author === null) {
            $author = Auth::user();
        }
        if ($author instanceof User) {
            $id = $author->id;
        } else {
            $id = $author;
        }
        $query->where('author_id', $id);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tasks extends Model
{
    use HasFactory;

    public function notes()
    {
        return $this->hasMany('App\Models\notes', 'task_id', 'tk_id');
    }
}

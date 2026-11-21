<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code'];
    public function members()
    {
        return $this->hasMany(User::class);
    }
}

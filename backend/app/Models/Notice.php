<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'read_at', 'invoice_id'];
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}

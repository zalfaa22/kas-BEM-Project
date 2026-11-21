<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['member_id', 'dues_rule_id', 'period_label', 'amount', 'due_date', 'status', 'notes'];
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_date' => 'date'];
    }
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
    public function rule()
    {
        return $this->belongsTo(DuesRule::class, 'dues_rule_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

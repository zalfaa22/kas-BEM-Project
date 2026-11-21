<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'member_id', 'amount', 'paid_at', 'method', 'status', 'rejection_reason', 'verified_by', 'verified_at'];
    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'verified_at' => 'datetime', 'amount' => 'decimal:2'];
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
    public function proof()
    {
        return $this->hasOne(PaymentProof::class);
    }
}

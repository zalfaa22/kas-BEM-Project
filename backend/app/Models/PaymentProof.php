<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    protected $fillable = ['original_name', 'path_file', 'file_size', 'uploaded_at', 'payment_id'];
    protected function casts(): array
    {
        return ['file_size' => 'integer', 'uploaded_at' => 'datetime'];
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

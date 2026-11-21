<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $t) {
            $t->id();
            $t->enum('type', ['pemasukan', 'pengeluaran']);
            $t->string('category');
            $t->string('description');
            $t->decimal('amount', 14, 2);
            $t->date('transaction_date');
            $t->foreignId('payment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });
        Schema::create('notices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->text('body');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notices');
        Schema::dropIfExists('cash_transactions');
    }
};

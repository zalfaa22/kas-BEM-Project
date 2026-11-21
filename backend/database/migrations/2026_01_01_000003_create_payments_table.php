<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // TransaksiKas
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('method')->default('transfer');
            $table->enum('status', ['menunggu_verifikasi', 'diterima', 'ditolak'])->default('menunggu_verifikasi');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id(); // BuktiPembayaran
            $table->string('original_name');
            $table->string('path_file');
            $table->unsignedInteger('file_size');
            $table->timestamp('uploaded_at');
            $table->foreignId('payment_id')->unique()->constrained('payments')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payments');
    }
};

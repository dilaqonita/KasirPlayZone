<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->integer('visitor_count')->default(1);
            $table->date('visit_date');
            $table->string('visit_time');                 // contoh: "13:00"
            $table->string('payment_method');             // cash | bank | ewallet | debit
            $table->integer('total_amount');              // Rupiah
            $table->string('status')->default('pending'); // pending | lunas | checkin | selesai | cancel
            $table->string('notes')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

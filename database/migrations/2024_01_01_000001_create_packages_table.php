<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emoji')->default('🎫');
            $table->text('description')->nullable();
            $table->string('age_label')->nullable();       // contoh: "3–6 Thn"
            $table->integer('duration_minutes');           // contoh: 60 = 1 jam
            $table->integer('price');                      // dalam Rupiah
            $table->json('features')->nullable();          // array fitur: ["Trampolin","Mandi Bola"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

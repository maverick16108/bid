<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $慶) {
            $慶->id();
            $慶->foreignId('user_id')->constrained()->onDelete('cascade');
            $慶->string('type'); // 'armature' or 'transport'
            $慶->string('status')->default('new'); // new, in_progress, completed, rejected
            $慶->json('details'); // Specific fields for each type
            $慶->text('comment')->nullable();
            $慶->string('external_id_1c')->nullable(); // For sync with 1C
            $慶->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number', 20)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('citizen_name', 80)->nullable();
            $table->string('category', 80);
            $table->string('location', 160);
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('Pending')->index();
            $table->timestamps();
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

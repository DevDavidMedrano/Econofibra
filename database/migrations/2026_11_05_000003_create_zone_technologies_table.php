<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_technologies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('zone_id')->constrained('coverage_zones')->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained('technologies');
            $table->string('status');
            $table->boolean('is_sample')->default(false);
            $table->timestamps();

            $table->unique(['zone_id', 'technology_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_technologies');
    }
};

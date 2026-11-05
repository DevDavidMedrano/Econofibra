<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_plan_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('zone_id')->constrained('coverage_zones')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            // Nullable and signed so an incomplete price can be stored; the domain filters it.
            $table->integer('price_cents')->nullable();
            $table->integer('installation_cost_cents')->nullable();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();

            $table->unique(['zone_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_plan_prices');
    }
};

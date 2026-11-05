<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Real catalog, with no relation to plans. Nullable and signed so an incomplete
        // card can be stored; the domain filters it.
        Schema::create('plan_cards', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('speed_mbps')->nullable();
            $table->string('phrase')->nullable();
            $table->string('icon_key')->nullable();
            $table->integer('position')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_cards');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** On SQLite, changing a column inside a transaction would cascade over the related rows. */
    public $withinTransaction = false;

    public function up(): void
    {
        // The rule is written here on purpose: a migration must not change with the domain.
        $keys = DB::table('coverage_zones')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->map(static fn (string $name): string => hash('sha256', mb_strtolower(trim($name), 'UTF-8')));

        $this->stopOnRepeatedNames($keys->all());

        Schema::table('coverage_zones', function (Blueprint $table): void {
            $table->char('name_key', 64)->nullable()->after('name');
            $table->boolean('boundary_replaced')->default(false)->after('boundary');
        });

        foreach ($keys as $id => $key) {
            DB::table('coverage_zones')->where('id', $id)->update(['name_key' => $key]);
        }

        Schema::table('coverage_zones', function (Blueprint $table): void {
            $table->char('name_key', 64)->nullable(false)->change();
            $table->unique('name_key');
        });
    }

    public function down(): void
    {
        Schema::table('coverage_zones', function (Blueprint $table): void {
            $table->dropUnique(['name_key']);
            $table->dropColumn(['name_key', 'boundary_replaced']);
        });
    }

    /**
     * Runs before any change to the table, so the migration can be repeated once they are fixed.
     *
     * @param  array<int, string>  $keys
     */
    private function stopOnRepeatedNames(array $keys): void
    {
        $repeated = array_keys(array_diff_key($keys, array_unique($keys)));

        if ($repeated !== []) {
            $names = DB::table('coverage_zones')->whereIn('id', $repeated)->pluck('name')->implode(', ');

            throw new RuntimeException("Hay zonas con el mismo nombre; corrígelas antes de migrar: {$names}");
        }
    }
};

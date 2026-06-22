<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preserve order history when a customer deletes their account: detach the
     * customer (user_id → null) instead of cascading the orders away.
     */
    public function up(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropForeign(['user_id']));

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropForeign(['user_id']));

        Schema::table('orders', function (Blueprint $table) {
            // Reverting to NOT NULL fails if any detached (null-user) orders exist.
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

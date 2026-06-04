<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('species');
            $table->unsignedTinyInteger('age')->nullable()->after('gender');
            $table->string('color')->nullable()->after('age');
            $table->unsignedSmallInteger('arrived_year')->nullable()->after('color');
            $table->json('personality')->nullable()->after('description');
            $table->string('favourite_treat')->nullable()->after('personality');
            $table->string('favourite_spot')->nullable()->after('favourite_treat');
            $table->json('fun_facts')->nullable()->after('favourite_spot');
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'age', 'color', 'arrived_year',
                'personality', 'favourite_treat', 'favourite_spot', 'fun_facts',
            ]);
        });
    }
};

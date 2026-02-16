<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ch_messages', function (Blueprint $table) {
            $table->string('to_type', 16)->default('user')->after('to_id');
        });
    }

    public function down(): void
    {
        Schema::table('ch_messages', function (Blueprint $table) {
            $table->dropColumn('to_type');
        });
    }
};

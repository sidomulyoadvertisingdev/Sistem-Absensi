<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ch_group_user', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 16)->default('member');
            $table->timestamps();

            $table->primary(['group_id', 'user_id']);
            $table->index(['user_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ch_group_user');
    }
};

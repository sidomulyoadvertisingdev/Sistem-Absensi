<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ch_message_reads', function (Blueprint $table) {
            $table->uuid('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->primary(['message_id', 'user_id']);
            $table->index(['user_id', 'message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ch_message_reads');
    }
};

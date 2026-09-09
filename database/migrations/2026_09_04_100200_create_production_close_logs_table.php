<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_close_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_todo_id')->nullable()->constrained('job_todos')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedBigInteger('erp_item_id');
            $table->unsignedBigInteger('erp_order_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();

            $table->boolean('order_completed')->default(false);
            $table->text('note')->nullable();

            $table->string('foto_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('captured_at')->nullable();

            $table->json('erp_response')->nullable();

            $table->timestamps();

            $table->index('erp_item_id');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_close_logs');
    }
};

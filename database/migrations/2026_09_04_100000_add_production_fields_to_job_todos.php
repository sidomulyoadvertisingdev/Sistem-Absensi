<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_todos', function (Blueprint $table) {
            // general = job biasa (dibuat admin) ; production = jembatan ke item produksi ERP
            $table->string('type', 20)->default('general')->after('id');
            $table->string('source', 20)->nullable()->after('type'); // 'pos' untuk job produksi

            $table->unsignedBigInteger('erp_item_id')->nullable()->unique()->after('source');
            $table->unsignedBigInteger('erp_order_id')->nullable()->after('erp_item_id');
            $table->string('invoice_no')->nullable()->after('erp_order_id');
            $table->unsignedBigInteger('outlet_id')->nullable()->after('invoice_no');

            $table->string('customer_name')->nullable()->after('outlet_id');
            $table->string('customer_phone', 40)->nullable()->after('customer_name');
            $table->string('product_name')->nullable()->after('customer_phone');
            $table->string('qty', 40)->nullable()->after('product_name');
            $table->timestamp('deadline_at')->nullable()->after('qty');
            $table->string('production_status', 30)->nullable()->after('deadline_at');
            $table->text('spec_summary')->nullable()->after('production_status');

            $table->index(['type', 'status']);
            $table->index('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_todos', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['outlet_id']);
            $table->dropUnique(['erp_item_id']);
            $table->dropColumn([
                'type', 'source', 'erp_item_id', 'erp_order_id', 'invoice_no', 'outlet_id',
                'customer_name', 'customer_phone', 'product_name', 'qty', 'deadline_at',
                'production_status', 'spec_summary',
            ]);
        });
    }
};

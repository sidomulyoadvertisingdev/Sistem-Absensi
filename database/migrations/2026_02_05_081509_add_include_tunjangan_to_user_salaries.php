<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('user_salaries', function ($table) {
        $table->boolean('include_tunjangan')->default(true);
    });
}

public function down()
{
    Schema::table('user_salaries', function ($table) {
        $table->dropColumn('include_tunjangan');
    });
}

};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable(false)->change();
        });
    }
};

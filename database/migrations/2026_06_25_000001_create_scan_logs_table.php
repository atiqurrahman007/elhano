<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 100);                  // scanned barcode value
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('variable_id')->nullable(); // null = simple product
            $table->string('action', 30);                    // 'receive' | 'adjust' | 'lookup'
            $table->integer('qty_before')->default(0);
            $table->integer('qty_change')->default(0);       // + add, - remove
            $table->integer('qty_after')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('barcode');
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scan_logs');
    }
};

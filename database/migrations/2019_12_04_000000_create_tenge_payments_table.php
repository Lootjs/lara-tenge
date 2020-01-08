<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTengePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('payment_id')->unique();
            $table->integer('amount');
            $table->integer('status');
            $table->string('driver');
            $table->json('data');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            //$table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->getTable());
    }

    /**
     * Returns the table name.
     *
     * @return string
     */
    public function getTable()
    {
        return config('tenge.table_name');
    }
}

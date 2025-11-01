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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable(); // opsional
            $table->string('source'); // gaji, freelance, bonus, dll
            $table->decimal('amount', 14, 0)->default(0);
            $table->date('date');
            $table->text('note')->nullable();
            $table->enum('allocation_mode',['tbb','direct'])->default('tbb');
            $table->unsignedTinyInteger('target_month')->nullable();
            $table->unsignedSmallInteger('target_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};

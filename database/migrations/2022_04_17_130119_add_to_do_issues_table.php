<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('to_do_issues', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name',124);
            $table->text('description');
            $table->integer('priority')->default(1);
            $table->enum('status', ['done','todo'])->default('todo');
            $table->timestamps();

//            $table->foreign('parent_id')->references('id')->on('to_do_issues');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('to_do_issues');
    }
};

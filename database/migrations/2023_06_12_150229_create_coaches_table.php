<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->increments('id');  // первичный ключ

            $table->string('surname',45);
            $table->string('name',45);
            $table->string('patronymic',45);

            $table->string('passport',10)->unique();

            $table->date("birth");//Дата рождения

            $table->string("number")->unique();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('registration',255);

            //признак продает ли тренер абонементы на персональные тренеровки
            $table->boolean('sale')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};

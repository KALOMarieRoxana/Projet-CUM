<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications_admin', function (Blueprint $table) {
            $table->id();
            $table->string('type');                       // 'demande_recue'
            $table->string('titre');
            $table->text('message');
            $table->foreignId('demande_id')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('lue')->default(false);
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications_admin');
    }
};
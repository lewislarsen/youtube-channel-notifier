<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel_id')->comment('The ID of the channel')->unique();
            $table->dateTime('last_checked_at')->nullable();
            $table->timestamps();
        });
    }
};

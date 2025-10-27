<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_settings', function (Blueprint $table) {
            $table->id();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('welcome_logo')->nullable();
            $table->string('title_welcome')->nullable();
            $table->text('description_welcome')->nullable();
            $table->string('why_choose_logo')->nullable();
            $table->string('why_choose_title')->nullable();
            $table->string('why_choose_title1')->nullable();
            $table->text('why_choose_description1')->nullable();
            $table->string('why_choose_title2')->nullable();
            $table->text('why_choose_description2')->nullable();
            $table->string('why_choose_title3')->nullable();
            $table->text('why_choose_description3')->nullable();
            $table->string('why_choose_title4')->nullable();
            $table->text('why_choose_description4')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_settings');
    }
};

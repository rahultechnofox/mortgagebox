<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdviceAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advice_areas', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('service_type');
            $table->integer('request_time');
            $table->integer('property');
            $table->integer('property_want');
            $table->integer('size_want');
            $table->integer('combined_income');
            $table->string('description');
            $table->integer('occupation');
            $table->integer('contact_preference');
            $table->integer('advisor_preference');
            $table->integer('fees_preference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advice_areas');
    }
}

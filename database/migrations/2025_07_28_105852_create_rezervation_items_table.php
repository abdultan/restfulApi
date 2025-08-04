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
        Schema::create('rezervation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Rezervation::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Seat::class)->constrained()->onDelete('cascade');
            $table->unsignedInteger('price');
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
        Schema::dropIfExists('rezervation_items');
    }
};

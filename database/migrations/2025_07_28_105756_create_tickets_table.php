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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Rezervation::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Seat::class)->constrained()->onDelete('cascade');
<<<<<<< HEAD
            $table->string('status')->default('available');
            $table->string('ticket_code')->nullable();
            $table->timestamps();
=======
            $table->string('status')->default('active');
            $table->string('ticket_code')->nullable()->unique();
            $table->timestamps();
            $table->unique(['rezervation_id', 'seat_id']);
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};

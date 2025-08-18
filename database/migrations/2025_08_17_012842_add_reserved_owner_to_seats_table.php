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
        Schema::table('seats', function (Blueprint $t) {
            // Koltuğu tutan kullanıcı
            $t->foreignId('reserved_by')
              ->nullable()
              ->constrained('users')   // users.id ile FK
              ->nullOnDelete();        // user silinirse null olsun

            // Son kullanım zamanı (hold süresi)
            $t->timestamp('reserved_until')->nullable();

            // Sık sorgular için küçük index
            $t->index(['status', 'reserved_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $t) {
            $t->dropConstrainedForeignId('reserved_by');
            $t->dropColumn('reserved_until');
        });
    }
};

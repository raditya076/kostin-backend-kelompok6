<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembagian_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_transaksi', 12, 2);
            $table->decimal('persen_platform', 5, 2)->default(3.00);
            $table->decimal('biaya_platform', 12, 2);
            $table->decimal('biaya_gateway', 12, 2);
            $table->decimal('jatah_pemilik', 12, 2);
            $table->enum('status_disbursement', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembagian_dana');
    }
};
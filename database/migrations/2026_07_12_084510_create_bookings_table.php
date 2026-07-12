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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('penyewa_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_kamar')->nullable();
            $table->date('tanggal_masuk');
            $table->integer('durasi_bulan');
            $table->date('tanggal_keluar');
            $table->decimal('harga_per_bulan', 12, 2);
            $table->decimal('total_harga', 12, 2);
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->enum('metode_pembayaran', ['transfer_bank', 'ewallet', 'qris'])->nullable();
            $table->string('payment_type')->nullable();
            $table->string('midtrans_status')->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->enum('status', ['menunggu_pembayaran', 'dibayar', 'aktif', 'ditolak', 'selesai', 'dibatalkan'])->default('menunggu_pembayaran');
            $table->text('catatan_penyewa')->nullable();
            $table->text('catatan_pemilik')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
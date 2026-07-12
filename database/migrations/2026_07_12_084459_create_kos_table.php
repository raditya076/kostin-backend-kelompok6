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
        Schema::create('kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_kos');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['putra', 'putri', 'campur']);
            $table->text('alamat');
            $table->string('kecamatan');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('kode_pos', 10)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->decimal('harga_per_bulan', 12, 2);
            $table->integer('jumlah_kamar');
            $table->integer('kamar_terisi')->default(0);
            $table->boolean('ada_nomor_kamar')->default(false);
            $table->boolean('wifi')->default(false);
            $table->boolean('ac')->default(false);
            $table->boolean('kamar_mandi_dalam')->default(false);
            $table->boolean('parkir')->default(false);
            $table->boolean('dapur')->default(false);
            $table->boolean('laundry')->default(false);
            $table->boolean('security')->default(false);
            $table->boolean('cctv')->default(false);
            $table->string('foto_utama')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kos');
    }
};
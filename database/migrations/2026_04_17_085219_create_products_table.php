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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion');
            $table->decimal('precio', 8, 2);
            $table->integer('stock');
            $table->string('talla')->nullable(); // Para camisetas
            $table->string('material')->nullable(); // Para figuras
            $table->string('dimensiones')->nullable(); // Para figuras
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

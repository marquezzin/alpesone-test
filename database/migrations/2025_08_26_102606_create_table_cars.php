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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('brand');
            $table->string('model');
            $table->string('version');
            $table->string('year_model');
            $table->string('year_build');
            $table->unsignedTinyInteger('doors')->nullable();
            $table->string('board');
            $table->string('chassi')->nullable();
            $table->string('transmission');
            $table->string('km')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created')->nullable();
            $table->timestamp('updated')->nullable();
            $table->boolean('sold')->default(false);
            $table->string('category')->nullable();
            $table->string('url_car')->unique(); // Vai ser meu parametro de unicidade, já que o id é externo
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->string('fuel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};

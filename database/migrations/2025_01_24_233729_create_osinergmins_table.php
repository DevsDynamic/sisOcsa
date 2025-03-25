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
        Schema::create('osinergmins', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable(); //ID UNIDAD EN API OCSA
            $table->string('plate')->nullable(); //PLACA DE UNIDAD
            $table->string('event')->nullable(); //EVENTO
            $table->string('speed')->nullable(); //VELOCIDAD
            $table->string('latitude')->nullable(); //LATITUD
            $table->string('longitude')->nullable(); //LONGITUD
            $table->string('gpsDate')->nullable(); //FECHA EN API OCSA
            $table->string('odometer')->nullable(); //KILOMETRAJE
            $table->string('response_timestamp')->nullable(); //FECHA DE RESPUESTA DE OSINERGMIN
            $table->string('response_message')->nullable(); //MENSAJE DE RESPUESTA DE OSINERGMIN
            $table->string('response_suggestion')->nullable(); //SUGERENCIA DE RESPUESTA DE OSINERGMIN
            $table->string('response_status')->nullable(); //ESTADO DE RESPUESTA DE OSINERGMIN

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osinergmins');
    }
};

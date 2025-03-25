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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('set null'); // Usuario de acceso al sistema
            $table->foreignId('type_document_id')->nullable()->constrained('type_documents')->nullOnDelete(); // Tipo de documento
            $table->string('document_number')->nullable()->unique(); // DNI-RUC del cliente
            $table->string('full_name'); // Nombre y apellido
            $table->date('birthdate')->nullable(); // Fecha de cumpleaños
            $table->string('address')->nullable(); // Dirección
            $table->string('email')->nullable()->unique(); // Correo
            $table->string('phone_number')->nullable()->nullable(); // Número de teléfono
            $table->foreignId('type_person_id')->nullable()->constrained('type_people')->nullOnDelete(); // Tipo de persona
            $table->string('token')->nullable(); // Token de API GPS OCSA

            $table->boolean('status')->default(true); //ACTIVO-INACTIVO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};

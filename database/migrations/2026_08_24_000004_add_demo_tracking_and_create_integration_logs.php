<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('token')->index();
        });

        Schema::table('osinergmins', function (Blueprint $table) {
            $table->foreignId('person_id')->nullable()->after('id')->constrained('people')->nullOnDelete();
            $table->string('environment', 20)->default('development')->after('person_id')->index();
        });

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('environment', 20)->index();
            $table->string('stage', 30)->index();
            $table->string('status', 20)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
            $table->index(['environment', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('person_id');
            $table->dropColumn('environment');
        });
        Schema::table('people', fn (Blueprint $table) => $table->dropColumn('is_demo'));
    }
};

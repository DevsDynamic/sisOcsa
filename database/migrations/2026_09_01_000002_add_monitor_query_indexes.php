<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('integration_logs', function (Blueprint $table) {
            $table->index(['environment', 'status', 'id'], 'integration_logs_environment_status_id_index');
        });
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->index(['environment', 'response_status', 'id'], 'osinergmins_environment_response_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('integration_logs', fn (Blueprint $table) => $table->dropIndex('integration_logs_environment_status_id_index'));
        Schema::table('osinergmins', fn (Blueprint $table) => $table->dropIndex('osinergmins_environment_response_id_index'));
    }
};

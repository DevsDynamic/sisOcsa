<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->index(['uuid', 'created_at', 'id'], 'osinergmins_uuid_created_id_index');
            $table->index(['response_status', 'created_at'], 'osinergmins_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->dropIndex('osinergmins_uuid_created_id_index');
            $table->dropIndex('osinergmins_status_created_index');
        });
    }
};

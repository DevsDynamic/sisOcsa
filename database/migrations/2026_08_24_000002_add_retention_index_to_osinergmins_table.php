<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'osinergmins_created_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->dropIndex('osinergmins_created_id_index');
        });
    }
};

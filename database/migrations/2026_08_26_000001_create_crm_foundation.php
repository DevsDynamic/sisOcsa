<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('lead_source', 100)->nullable()->after('token');
            $table->text('commercial_notes')->nullable()->after('lead_source');
            $table->boolean('marketing_consent')->default(false)->after('commercial_notes')->index();
            $table->timestamp('marketing_consent_at')->nullable()->after('marketing_consent');
            $table->timestamp('marketing_opt_out_at')->nullable()->after('marketing_consent_at');
            $table->timestamp('converted_at')->nullable()->after('marketing_opt_out_at');
        });

        Schema::create('type_person_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_type_person_id')->constrained('type_people')->cascadeOnDelete();
            $table->foreignId('to_type_person_id')->constrained('type_people')->cascadeOnDelete();
            $table->string('name');
            $table->json('required_fields')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['from_type_person_id', 'to_type_person_id'], 'type_person_transition_unique');
        });

        Schema::create('person_type_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('from_type_person_id')->nullable()->constrained('type_people')->nullOnDelete();
            $table->foreignId('to_type_person_id')->constrained('type_people')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['person_id', 'created_at']);
        });

        $cp = DB::table('type_people')->whereRaw('LOWER(code) = ?', ['cp'])->value('id');
        $co = DB::table('type_people')->whereRaw('LOWER(code) = ?', ['co'])->value('id');
        if ($cp && $co) {
            DB::table('type_person_transitions')->insert([
                'from_type_person_id' => $cp,
                'to_type_person_id' => $co,
                'name' => 'Convertir prospecto en contacto',
                'required_fields' => json_encode(['type_document_id', 'document_number', 'email']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('person_type_histories');
        Schema::dropIfExists('type_person_transitions');
        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['marketing_consent']);
            $table->dropColumn(['lead_source', 'commercial_notes', 'marketing_consent', 'marketing_consent_at', 'marketing_opt_out_at', 'converted_at']);
        });
    }
};

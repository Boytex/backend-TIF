
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Permite actualizar una base donde ya se ejecutaron las migraciones originales,
// que creaban incidents y comments sin sus columnas funcionales.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incidents')) {
            Schema::table('incidents', function (Blueprint $table) {
                if (! Schema::hasColumn('incidents', 'title')) $table->string('title')->default('Incidencia importada');
                if (! Schema::hasColumn('incidents', 'description')) $table->text('description')->default('Sin descripción');
                if (! Schema::hasColumn('incidents', 'status')) $table->string('status', 30)->default('ABIERTA');
                if (! Schema::hasColumn('incidents', 'user_id')) $table->unsignedBigInteger('user_id')->nullable();
                if (! Schema::hasColumn('incidents', 'technician_id')) $table->unsignedBigInteger('technician_id')->nullable();
                if (! Schema::hasColumn('incidents', 'category_id')) $table->unsignedBigInteger('category_id')->nullable();
                if (! Schema::hasColumn('incidents', 'resolution')) $table->text('resolution')->nullable();
            });
        }

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (! Schema::hasColumn('comments', 'incident_id')) $table->unsignedBigInteger('incident_id')->nullable();
                if (! Schema::hasColumn('comments', 'user_id')) $table->unsignedBigInteger('user_id')->nullable();
                if (! Schema::hasColumn('comments', 'comment')) $table->text('comment')->default('Comentario importado');
            });
        }
    }

    public function down(): void
    {
        // No se eliminan columnas en el rollback para preservar datos existentes.
    }
};

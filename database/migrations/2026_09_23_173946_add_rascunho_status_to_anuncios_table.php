<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a "rascunho" (draft) status to the anuncios.status enum, needed so the
 * CRM's "Duplicar anúncio" action can create a copy that is never published
 * automatically. Existing rows/values are untouched - this only widens the
 * allowed set.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (testes) guarda o enum como texto e já aceita o valor novo.
        // Em MySQL o ALTER é o que alarga o enum — o SQL fica igual ao de produção.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE anuncios MODIFY status ENUM('rascunho', 'pendente', 'aprovado', 'rejeitado', 'expirado') NOT NULL DEFAULT 'pendente'");
    }

    public function down(): void
    {
        DB::statement("UPDATE anuncios SET status = 'pendente' WHERE status = 'rascunho'");

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE anuncios MODIFY status ENUM('pendente', 'aprovado', 'rejeitado', 'expirado') NOT NULL DEFAULT 'pendente'");
    }
};

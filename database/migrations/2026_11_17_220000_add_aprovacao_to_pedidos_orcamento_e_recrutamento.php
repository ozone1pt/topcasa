<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pré-aprovação no CRM para pedidos de orçamento e vagas de recrutamento,
 * igual aos anúncios: novos registos começam em "pendente" e só ficam
 * públicos (e só disparam os emails) depois de "Aprovar" no CRM.
 *
 * - pedidos_orcamento.status: + 'pendente', 'rejeitado' (default 'pendente').
 *   'validado' continua a significar aprovado/publicado.
 * - pedidos_recrutamento.status: + 'pendente', 'rejeitado' (default 'pendente').
 *   'ativo' continua a significar aprovado/publicado.
 * - motivo_rejeicao (text, nullable) nas duas tabelas, como em anuncios.
 *
 * Os registos existentes não são alterados (continuam aprovados).
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite não tem ALTER ... MODIFY ENUM. O enum lá é texto, por isso os
        // valores novos já cabem; em MySQL o statement é o de produção.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pedidos_orcamento MODIFY status ENUM('pendente', 'validado', 'rejeitado', 'concluido') NOT NULL DEFAULT 'pendente'");
            DB::statement("ALTER TABLE pedidos_recrutamento MODIFY status ENUM('pendente', 'ativo', 'pausado', 'rejeitado', 'cancelado') NOT NULL DEFAULT 'pendente'");
        }

        Schema::table('pedidos_orcamento', function (Blueprint $table) {
            $table->text('motivo_rejeicao')->nullable()->after('notas_admin');
        });

        Schema::table('pedidos_recrutamento', function (Blueprint $table) {
            $table->text('motivo_rejeicao')->nullable()->after('notas_admin');
        });
    }

    public function down(): void
    {
        // Pendentes/rejeitados nunca foram públicos: ficam escondidos com os
        // estados antigos (concluido / cancelado) em vez de serem publicados.
        DB::statement("UPDATE pedidos_orcamento SET status = 'concluido' WHERE status IN ('pendente', 'rejeitado')");
        DB::statement("UPDATE pedidos_recrutamento SET status = 'cancelado' WHERE status IN ('pendente', 'rejeitado')");

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pedidos_orcamento MODIFY status ENUM('validado', 'concluido') NOT NULL DEFAULT 'validado'");
            DB::statement("ALTER TABLE pedidos_recrutamento MODIFY status ENUM('ativo', 'pausado', 'cancelado') NOT NULL DEFAULT 'ativo'");
        }

        Schema::table('pedidos_orcamento', function (Blueprint $table) {
            $table->dropColumn('motivo_rejeicao');
        });

        Schema::table('pedidos_recrutamento', function (Blueprint $table) {
            $table->dropColumn('motivo_rejeicao');
        });
    }
};

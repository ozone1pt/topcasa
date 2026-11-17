<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the CRM decide who receives the automatic "novo pedido de
 * orçamento/recrutamento" emails, driven by real data on each user's row
 * instead of a hardcoded address list in code. Defaults to true so existing
 * approved professionals keep getting notified unless turned off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notificar_orcamentos')->default(true)->after('lembrete_anuncio_status');
            $table->boolean('notificar_recrutamento')->default(true)->after('notificar_orcamentos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notificar_orcamentos', 'notificar_recrutamento']);
        });
    }
};

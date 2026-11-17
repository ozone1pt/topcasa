<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * email_notificacoes was built for emails tied to a pedido (orçamento /
 * recrutamento). "Alguém guardou o teu contacto" emails belong to an
 * anúncio instead, so pedido_id becomes optional and anuncio_id is added -
 * that way every email the site sends ends up in the same log the CRM counts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_notificacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable()->change();
            $table->unsignedBigInteger('anuncio_id')->nullable()->after('pedido_id');
            $table->index(['tipo', 'enviado_em']);
        });
    }

    public function down(): void
    {
        Schema::table('email_notificacoes', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'enviado_em']);
            $table->dropColumn('anuncio_id');
        });
    }
};

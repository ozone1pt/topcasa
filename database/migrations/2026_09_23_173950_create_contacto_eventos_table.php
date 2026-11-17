<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-event contact log, timestamped - lets the CRM break contacts down "por
 * período" in a way the existing lifetime counters (anuncios.phone_clicks
 * etc.) can't. Those counters stay the source of truth for per-anúncio
 * totals shown elsewhere (Analytics, homepage stats); this table only adds
 * the time dimension on top, for the same underlying reveal actions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto_eventos', function (Blueprint $table) {
            $table->id();
            $table->enum('origem', ['telefone', 'whatsapp', 'site']);
            $table->foreignId('anuncio_id')->nullable()->constrained('anuncios')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pedido_orcamento_id')->nullable()->constrained('pedidos_orcamento')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['origem', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_eventos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log of every automatic CRM notification email dispatch attempt, so it's
 * possible to check afterwards whether a given pedido was actually
 * processed and how many recipients it reached.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30); // 'orcamento' | 'recrutamento'
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedInteger('destinatarios_count')->default(0);
            $table->boolean('sucesso')->default(false);
            $table->text('erro')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'pedido_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notificacoes');
    }
};

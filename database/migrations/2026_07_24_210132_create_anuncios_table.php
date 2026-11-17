<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('minha_empresa')->nullable();
            $table->boolean('certificacao_profissional')->default(false);
            $table->boolean('seguro_responsabilidade_civil')->default(false);
            $table->text('descricao')->nullable();
            $table->string('email')->nullable();
            $table->string('contacto', 50)->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->nullOnDelete();
            $table->foreignId('concelho_id')->nullable()->constrained('concelhos')->nullOnDelete();
            $table->decimal('preco', 10, 2)->nullable();
            $table->boolean('destacar')->default(false);
            $table->string('youtube_link')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado', 'expirado'])->default('pendente');
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamp('data_expiracao')->nullable();
            $table->timestamp('aviso_expiracao_enviado_em')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('whatsapp_clicks')->default(0);
            $table->unsignedInteger('phone_clicks')->default(0);
            $table->unsignedInteger('email_clicks')->default(0);
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncios');
    }
};

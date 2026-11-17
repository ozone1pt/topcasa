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
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anuncio_id')->constrained('anuncios')->cascadeOnDelete();
            $table->foreignId('remetente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remetente_nome')->nullable();
            $table->string('remetente_email')->nullable();
            $table->foreignId('destinatario_id')->constrained('users')->cascadeOnDelete();
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('mensagens')->cascadeOnDelete();
            $table->timestamp('remetente_deleted_at')->nullable();
            $table->timestamp('destinatario_deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensagens');
    }
};

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
        Schema::create('pedidos_orcamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo')->nullable();
            $table->string('nome');
            $table->string('telefone', 50);
            $table->string('email')->nullable();
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->nullOnDelete();
            $table->text('descricao');
            $table->string('foto')->nullable();
            $table->boolean('termo_aceite')->default(false);
            $table->enum('status', ['pendente', 'validado', 'rejeitado', 'concluido'])->default('pendente');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_orcamento');
    }
};

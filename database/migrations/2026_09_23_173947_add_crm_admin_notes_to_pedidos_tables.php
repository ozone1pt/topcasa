<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Informação administrativa interna" for the CRM's Orçamentos and
 * Recrutamento areas - kept separate from the fields the public submits so
 * the original submitted data is never touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_orcamento', function (Blueprint $table) {
            $table->text('notas_admin')->nullable()->after('status');
        });

        Schema::table('pedidos_recrutamento', function (Blueprint $table) {
            $table->text('notas_admin')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_orcamento', function (Blueprint $table) {
            $table->dropColumn('notas_admin');
        });

        Schema::table('pedidos_recrutamento', function (Blueprint $table) {
            $table->dropColumn('notas_admin');
        });
    }
};

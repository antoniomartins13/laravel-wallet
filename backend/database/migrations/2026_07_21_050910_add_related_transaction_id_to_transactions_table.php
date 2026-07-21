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
        Schema::table('transactions', function (Blueprint $table) {
            // Links the two legs of a transfer (transfer_out <-> transfer_in)
            // to each other, so a reversal requested from either side can
            // reliably locate its sibling without guessing by amount/time.
            $table->foreignUuid('related_transaction_id')
                ->nullable()
                ->after('related_wallet_id')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_transaction_id');
        });
    }
};

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
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->enum('position', ['debit', 'credit']);
            $table->unsignedDecimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('line_number')->default(1);
            $table->unique(['journal_entry_id', 'line_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};

<?php

use App\Models\Tag;
use App\Models\Transaction;
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
        Schema::create('tag_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tag::class);
            $table->foreignIdFor(Transaction::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_transaction');
    }
};

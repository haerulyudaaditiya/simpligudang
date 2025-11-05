<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('item_code')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('status')->default('in_stock'); // "in_stock", "in_use", "under_repair", "lost"
            $table->decimal('price', 15, 2)->nullable();
            $table->date('purchase_date')->nullable(); 
            $table->timestamps();
            $table->index(['team_id', 'item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

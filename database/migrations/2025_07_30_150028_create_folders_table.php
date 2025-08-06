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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string("folderName");
            $table->text("folderPath")->nullable();
            $table->enum('type', ['active', 'finished', 'jurisprudence']);
            $table->tinyInteger("important")->default(3);
            $table->foreignId('parentFolder')->nullable()->constrained('folders');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};

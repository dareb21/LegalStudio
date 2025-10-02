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
            $table->unsignedBigInteger('parentFolder')->nullable();
            $table->foreign('parentFolder')->references('id')->on('folders');
            $table->unique(['folderName', 'parentFolder']);
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->string('deleted_by_name')->nullable();
            $table->dateTime("hardDelete")->nullable();
             $table->boolean("hardDeleted")->nullable();
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

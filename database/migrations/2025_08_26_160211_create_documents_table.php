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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string("documentName");
            $table->foreignId('folder_id')->constrained();
            $table->text("folderPath");
            $table->text("description"); //posiblemente nullable
            $table->string("judge")->nullable();
            $table->string("whoMadeIt");
            $table->boolean("isSensitive")->default(0);
            $table->tinyInteger("important")->default(3);;
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->string('deleted_by_name')->nullable();
            $table->dateTime("hardDelete")->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

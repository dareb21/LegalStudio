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
        Schema::create('download_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained();
            $table->string("document_name");
            $table->dateTime("requestDate");
            $table->foreignId('requested_by')->constrained('users');
            $table->string("requested_by_name");
            $table->boolean("status")->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users');
            $table->dateTime("responseDate")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_requests');
    }
};

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
        Schema::table('cosplan', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::create('cosplan_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cosplan_id')->constrained('cosplan')->onDelete('cascade');
            $table->string('path');
            $table->enum('type', ['main', 'reference', 'progress']);
            $table->timestamps();

            $table->index('cosplan_id');
            $table->index('type');
        });

        Schema::create('cosplan_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cosplan_id')->constrained('cosplan')->onDelete('cascade');
            $table->enum('type', ['link', 'note']);
            $table->text('content');
            $table->timestamps();

            $table->index('cosplan_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cosplan_materials');
        Schema::dropIfExists('cosplan_images');

        Schema::table('cosplan', function (Blueprint $table) {
            $table->string('image_path')->nullable();
        });
    }
};

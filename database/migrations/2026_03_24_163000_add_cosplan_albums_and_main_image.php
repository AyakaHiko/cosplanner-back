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
        Schema::create('cosplan_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cosplan_id')->constrained('cosplan')->onDelete('cascade');
            $table->string('title');
            $table->timestamps();
        });

        Schema::table('cosplan', function (Blueprint $table) {
            $table->string('main_image_path')->nullable();
        });

        Schema::table('cosplan_images', function (Blueprint $table) {
            $table->foreignId('album_id')->nullable()->constrained('cosplan_albums')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cosplan_images', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
            $table->dropColumn('album_id');
        });

        Schema::table('cosplan', function (Blueprint $table) {
            $table->dropColumn('main_image_path');
        });

        Schema::dropIfExists('cosplan_albums');
    }
};

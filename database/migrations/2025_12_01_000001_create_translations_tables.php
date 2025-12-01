<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255)->index();
            $table->string('locale', 10)->index();
            $table->text('value');
            $table->timestamps();

            // Composite index for faster lookups
            $table->index(['key', 'locale']);
            $table->index(['locale', 'key']);

            // Unique constraint to prevent duplicate key-locale combinations
            $table->unique(['key', 'locale']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('translation_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes for faster joins
            $table->index('translation_id');
            $table->index('tag_id');

            // Prevent duplicate tag assignments
            $table->unique(['translation_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('translations');
    }
};

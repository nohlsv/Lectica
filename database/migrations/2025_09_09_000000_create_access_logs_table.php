<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('route');
            $table->string('method', 10);
            $table->timestamp('accessed_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};

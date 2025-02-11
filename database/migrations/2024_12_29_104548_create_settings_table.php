<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->mediumText('key')
                ->primary()
                ->unique('settings_un0');
            $table->longText('value');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //            $table->set('created_at')->default('CURRENT_TIMESTAMP');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

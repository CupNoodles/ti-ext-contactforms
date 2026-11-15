<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Original v3 table. Kept as-is so existing installs keep their migration history;
 * the v4 columns are added by 2026_11_15_000100_upgrade_contact_forms_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_forms')) {
            return;
        }

        Schema::create('contact_forms', function(Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->increments('contact_form_id');
            $table->string('name');
            $table->string('slug');
            $table->string('email');
            $table->string('subject');
            $table->text('form');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_forms');
    }
};

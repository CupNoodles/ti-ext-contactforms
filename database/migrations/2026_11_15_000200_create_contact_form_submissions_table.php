<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_form_submissions', function(Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->increments('submission_id');
            $table->unsignedInteger('contact_form_id')->nullable()->index();
            $table->string('email')->nullable();
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('newsletter_subscribed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_form_submissions');
    }
};

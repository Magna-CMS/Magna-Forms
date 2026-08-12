<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forms and their submissions. A form's fields are a JSON schema the
 * public endpoint validates against — the definition IS the validation,
 * so a field that isn't declared can never be stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms_forms', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->string('handle')->unique();
            $table->string('name');
            $table->json('fields');
            $table->string('submit_label')->default('Send');
            $table->text('success_message')->nullable();
            $table->json('notify_emails')->nullable();
            $table->boolean('store_submissions')->default(true);
            $table->timestamps();
        });

        Schema::create('forms_submissions', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->char('form_id', 26)->index();
            $table->json('data');
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('form_id')->references('id')->on('forms_forms')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms_submissions');
        Schema::dropIfExists('forms_forms');
    }
};

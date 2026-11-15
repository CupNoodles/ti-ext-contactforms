<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v4: forms are built from field definitions instead of hand-written HTML.
 *
 * v3 only ever emailed a fixed set of input names, so existing HTML forms are converted to
 * field definitions by finding those inputs (and their <label for> text) in the HTML. The
 * original HTML is kept in `legacy_form` for reference.
 */
return new class extends Migration
{
    /** v3 input name => [type, default label] */
    protected array $legacyInputs = [
        'first_name' => ['text', 'First Name'],
        'last_name' => ['text', 'Last Name'],
        'email_address' => ['email', 'Email Address'],
        'phone_number' => ['tel', 'Phone Number'],
        'contact_message' => ['textarea', 'Message'],
        'date_requested' => ['date', 'Requested Date'],
    ];

    public function up(): void
    {
        Schema::table('contact_forms', function(Blueprint $table): void {
            $table->renameColumn('email', 'recipients');
            $table->renameColumn('form', 'legacy_form');
        });

        Schema::table('contact_forms', function(Blueprint $table): void {
            $table->text('legacy_form')->nullable()->change();
            $table->string('recipients', 1000)->change();
            $table->json('fields')->nullable()->after('subject');
            $table->string('submit_label')->nullable();
            $table->text('success_message')->nullable();
            $table->boolean('newsletter_opt_in')->default(false);
            $table->string('newsletter_label')->nullable();
            $table->boolean('use_captcha')->default(false);
            $table->boolean('store_submissions')->default(true);
            $table->unsignedSmallInteger('throttle_limit')->default(5);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        DB::table('contact_forms')->orderBy('contact_form_id')->get()->each(function($form): void {
            [$fields, $newsletterLabel] = $this->convertLegacyHtml((string)$form->legacy_form);

            DB::table('contact_forms')->where('contact_form_id', $form->contact_form_id)->update([
                'fields' => json_encode($fields),
                'newsletter_opt_in' => !is_null($newsletterLabel),
                'newsletter_label' => $newsletterLabel ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('contact_forms', function(Blueprint $table): void {
            $table->dropColumn([
                'fields', 'submit_label', 'success_message', 'newsletter_opt_in', 'newsletter_label',
                'use_captcha', 'store_submissions', 'throttle_limit', 'status', 'created_at', 'updated_at',
            ]);
        });

        DB::table('contact_forms')->whereNull('legacy_form')->update(['legacy_form' => '']);

        Schema::table('contact_forms', function(Blueprint $table): void {
            $table->string('recipients', 255)->change();
            $table->text('legacy_form')->nullable(false)->change();
        });

        Schema::table('contact_forms', function(Blueprint $table): void {
            $table->renameColumn('recipients', 'email');
            $table->renameColumn('legacy_form', 'form');
        });
    }

    /**
     * @return array{0: array, 1: string|null} [field definitions, newsletter label or null]
     */
    protected function convertLegacyHtml(string $html): array
    {
        $found = [];
        foreach (array_keys($this->legacyInputs) as $name) {
            if (($position = $this->findInputPosition($html, $name)) !== null) {
                $found[$name] = $position;
            }
        }

        asort($found);

        $fields = [];
        foreach (array_keys($found) as $priority => $name) {
            [$type, $defaultLabel] = $this->legacyInputs[$name];
            $fields[] = [
                'priority' => $priority,
                'label' => $this->findLabel($html, $name) ?: $defaultLabel,
                'name' => $name,
                'type' => $type,
                'required' => $name === 'email_address',
                'width' => in_array($name, ['first_name', 'last_name'], true) ? 'half' : 'full',
                'placeholder' => '',
                'help' => '',
                'options' => '',
            ];
        }

        $newsletterLabel = null;
        if ($this->findInputPosition($html, 'subscribe_to_newsletter') !== null) {
            $newsletterLabel = $this->findLabel($html, 'subscribe_to_newsletter') ?? '';
        }

        return [$fields, $newsletterLabel];
    }

    protected function findInputPosition(string $html, string $name): ?int
    {
        if (preg_match('/name\s*=\s*["\']'.preg_quote($name, '/').'["\']/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            return $match[0][1];
        }

        return null;
    }

    protected function findLabel(string $html, string $name): ?string
    {
        // The tag carrying name="..." and its id
        if (!preg_match('/<(?:input|textarea|select)\b[^>]*name\s*=\s*["\']'.preg_quote($name, '/').'["\'][^>]*>/i', $html, $tag)) {
            return null;
        }

        if (!preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $tag[0], $id)) {
            return null;
        }

        if (!preg_match('/<label\b[^>]*for\s*=\s*["\']'.preg_quote($id[1], '/').'["\'][^>]*>(.*?)<\/label>/is', $html, $label)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($label[1])));

        return $text !== '' ? html_entity_decode($text, ENT_QUOTES) : null;
    }
};

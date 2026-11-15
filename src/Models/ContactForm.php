<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Models;

use Igniter\Flame\Database\Model;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * A contact form built in the admin.
 *
 * @property int $contact_form_id
 * @property string $name
 * @property string $slug
 * @property string $recipients Comma separated email addresses
 * @property string $subject
 * @property array|null $fields
 * @property string|null $submit_label
 * @property string|null $success_message
 * @property bool $newsletter_opt_in
 * @property string|null $newsletter_label
 * @property bool $use_captcha
 * @property bool $store_submissions
 * @property int $throttle_limit Submissions allowed per visitor per hour, 0 = unlimited
 * @property bool $status
 */
class ContactForm extends Model
{
    use Switchable;

    public const array FIELD_TYPES = [
        'text', 'email', 'tel', 'number', 'date', 'time', 'textarea', 'select', 'radio', 'checkbox',
    ];

    /** Field types that need a list of options. */
    public const array OPTION_TYPES = ['select', 'radio'];

    protected $table = 'contact_forms';

    protected $primaryKey = 'contact_form_id';

    public $timestamps = true;

    protected $fillable = [
        'name', 'slug', 'recipients', 'subject', 'fields', 'submit_label', 'success_message',
        'newsletter_opt_in', 'newsletter_label', 'use_captcha', 'store_submissions', 'throttle_limit', 'status',
    ];

    protected $casts = [
        'fields' => 'array',
        'newsletter_opt_in' => 'boolean',
        'use_captcha' => 'boolean',
        'store_submissions' => 'boolean',
        'throttle_limit' => 'integer',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'store_submissions' => true,
        'throttle_limit' => 5,
        'status' => true,
    ];

    public $relation = [
        'hasMany' => [
            'submissions' => [Submission::class, 'foreignKey' => 'contact_form_id', 'delete' => true],
        ],
    ];

    //
    // Events
    //

    protected function beforeSave()
    {
        $this->slug = Str::slug($this->slug ?: $this->name, '-');
        $this->fields = static::normalizeFields((array)$this->fields);
    }

    //
    // Options
    //

    public static function getFieldTypeOptions(): array
    {
        return collect(self::FIELD_TYPES)
            ->mapWithKeys(fn(string $type): array => [$type => 'lang:cupnoodles.contactforms::default.field_types.'.$type])
            ->all();
    }

    public static function getDropdownOptions(): array
    {
        return static::query()->orderBy('name')->pluck('name', 'contact_form_id')->all();
    }

    //
    // Helpers
    //

    public static function findBySlug(?string $slug): ?static
    {
        if (!$slug) {
            return null;
        }

        return static::query()->where('slug', $slug)->first();
    }

    /**
     * Cleans the field definitions coming from the admin repeater: gives every field a
     * unique machine name derived from its label, drops empty rows and parses options.
     */
    public static function normalizeFields(array $fields): array
    {
        $normalized = [];
        $usedNames = ['website', 'subscribe']; // reserved by the form component

        foreach (array_values($fields) as $index => $field) {
            $label = trim((string)($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $type = in_array($field['type'] ?? null, self::FIELD_TYPES, true) ? $field['type'] : 'text';

            $name = Str::snake(Str::ascii(trim((string)($field['name'] ?? ''))) ?: Str::ascii($label));
            $name = preg_replace('/[^a-z0-9_]/', '', strtolower($name)) ?: 'field';
            if (!preg_match('/^[a-z]/', $name)) {
                $name = 'field_'.$name;
            }

            $name = substr($name, 0, 48);
            $base = $name;
            for ($i = 2; in_array($name, $usedNames, true); $i++) {
                $name = $base.'_'.$i;
            }

            $usedNames[] = $name;

            $normalized[] = [
                'priority' => $index,
                'label' => $label,
                'name' => $name,
                'type' => $type,
                'required' => (bool)($field['required'] ?? false),
                'width' => ($field['width'] ?? 'full') === 'half' ? 'half' : 'full',
                'placeholder' => trim((string)($field['placeholder'] ?? '')),
                'help' => trim((string)($field['help'] ?? '')),
                'options' => in_array($type, self::OPTION_TYPES, true) ? (string)($field['options'] ?? '') : '',
            ];
        }

        return $normalized;
    }

    /**
     * Options for a select/radio field, one per line in the admin.
     */
    public static function parseOptions(array $field): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string)($field['options'] ?? '')))
            ->map(fn($option): string => trim((string)$option))
            ->filter(fn(string $option): bool => $option !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function getFormFields(): array
    {
        return array_values((array)$this->fields);
    }

    public function listRecipients(): array
    {
        return collect(preg_split('/[\s,;]+/', (string)$this->recipients))
            ->filter(fn($email): bool => (bool)filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Name of the first email field, used as the Reply-To and for newsletter sign-ups.
     */
    public function getEmailFieldName(): ?string
    {
        foreach ($this->getFormFields() as $field) {
            if ($field['type'] === 'email') {
                return $field['name'];
            }
        }

        return null;
    }

    public function showsNewsletterOptIn(): bool
    {
        return $this->newsletter_opt_in && !is_null($this->getEmailFieldName());
    }

    /**
     * Validation rules for a submission, keyed as "data.<field name>".
     */
    public function makeValidationRules(): array
    {
        $rules = [];
        foreach ($this->getFormFields() as $field) {
            $fieldRules = [$field['required'] ? 'required' : 'nullable'];

            $fieldRules = array_merge($fieldRules, match ($field['type']) {
                'email' => ['email:filter', 'max:255'],
                'tel' => ['string', 'max:40', 'regex:/^[0-9+().\-\s\/x]*$/i'],
                'number' => ['numeric'],
                'date' => ['date'],
                'time' => ['date_format:H:i'],
                'textarea' => ['string', 'max:5000'],
                'select', 'radio' => ['string', Rule::in(static::parseOptions($field))],
                'checkbox' => ['boolean'],
                default => ['string', 'max:255'],
            });

            if ($field['type'] === 'checkbox' && $field['required']) {
                $fieldRules = ['accepted'];
            }

            $rules['data.'.$field['name']] = $fieldRules;
        }

        return $rules;
    }

    public function makeValidationAttributes(): array
    {
        return collect($this->getFormFields())
            ->mapWithKeys(fn(array $field): array => ['data.'.$field['name'] => $field['label']])
            ->all();
    }
}

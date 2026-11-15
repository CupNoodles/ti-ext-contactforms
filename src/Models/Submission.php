<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Models;

use Igniter\Flame\Database\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A message sent through a contact form.
 *
 * @property int $submission_id
 * @property int|null $contact_form_id
 * @property string|null $email
 * @property array $data List of ['name', 'label', 'value'] in form order, labels as they were when sent
 * @property string|null $ip_address
 * @property bool $newsletter_subscribed
 * @property Carbon $created_at
 * @property-read ContactForm|null $contact_form
 */
class Submission extends Model
{
    protected $table = 'contact_form_submissions';

    protected $primaryKey = 'submission_id';

    public $timestamps = true;

    protected $fillable = ['contact_form_id', 'email', 'data', 'ip_address', 'newsletter_subscribed'];

    protected $casts = [
        'contact_form_id' => 'integer',
        'data' => 'array',
        'newsletter_subscribed' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'contact_form' => [ContactForm::class, 'foreignKey' => 'contact_form_id'],
        ],
    ];

    /**
     * Pairs each form field with the submitted value, formatted for people to read.
     */
    public static function buildData(ContactForm $form, array $values): array
    {
        $data = [];
        foreach ($form->getFormFields() as $field) {
            $value = $values[$field['name']] ?? null;

            if ($field['type'] === 'checkbox') {
                $value = $value
                    ? lang('cupnoodles.contactforms::default.text_yes')
                    : lang('cupnoodles.contactforms::default.text_no');
            }

            $data[] = [
                'name' => $field['name'],
                'label' => $field['label'],
                'value' => is_scalar($value) ? trim((string)$value) : '',
            ];
        }

        return $data;
    }

    public function getFormNameAttribute(): string
    {
        return $this->contact_form?->name ?? lang('cupnoodles.contactforms::default.text_deleted_form');
    }

    /**
     * Short preview for the submissions list: the longest answer, usually the message.
     */
    public function getSummaryAttribute(): string
    {
        $longest = collect((array)$this->data)
            ->pluck('value')
            ->sortByDesc(fn($value): int => mb_strlen((string)$value))
            ->first();

        return Str::limit((string)$longest, 80);
    }
}

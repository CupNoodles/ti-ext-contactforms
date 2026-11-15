<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Http\Requests;

use CupNoodles\ContactForms\Models\ContactForm;
use Igniter\System\Classes\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContactFormRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'name' => lang('cupnoodles.contactforms::default.label_name'),
            'slug' => lang('cupnoodles.contactforms::default.label_slug'),
            'recipients' => lang('cupnoodles.contactforms::default.label_recipients'),
            'subject' => lang('cupnoodles.contactforms::default.label_subject'),
            'fields' => lang('cupnoodles.contactforms::default.label_fields'),
            'fields.*.label' => lang('cupnoodles.contactforms::default.label_field_label'),
            'fields.*.name' => lang('cupnoodles.contactforms::default.label_field_name'),
            'fields.*.type' => lang('cupnoodles.contactforms::default.label_field_type'),
            'fields.*.options' => lang('cupnoodles.contactforms::default.label_field_options'),
            'submit_label' => lang('cupnoodles.contactforms::default.label_submit_label'),
            'success_message' => lang('cupnoodles.contactforms::default.label_success_message'),
            'newsletter_label' => lang('cupnoodles.contactforms::default.label_newsletter_label'),
            'throttle_limit' => lang('cupnoodles.contactforms::default.label_throttle_limit'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:2,255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255',
                Rule::unique('contact_forms', 'slug')->ignore($this->getRecordId(), 'contact_form_id'),
            ],
            'recipients' => ['required', 'string', 'max:1000'],
            'subject' => ['required', 'string', 'max:255'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.name' => ['nullable', 'string', 'max:48', 'regex:/^[a-zA-Z0-9_\- ]*$/'],
            'fields.*.type' => ['required', Rule::in(ContactForm::FIELD_TYPES)],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.width' => ['nullable', 'in:full,half'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.help' => ['nullable', 'string', 'max:500'],
            'fields.*.options' => ['nullable', 'string', 'max:5000'],
            'submit_label' => ['nullable', 'string', 'max:255'],
            'success_message' => ['nullable', 'string', 'max:2000'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
            'newsletter_label' => ['nullable', 'string', 'max:255'],
            'use_captcha' => ['nullable', 'boolean'],
            'store_submissions' => ['nullable', 'boolean'],
            'throttle_limit' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Catch the mistakes that would otherwise only show up when a customer submits the form.
     */
    public function after(): array
    {
        return [
            function(Validator $validator): void {
                $recipients = preg_split('/[\s,;]+/', (string)$this->input('recipients'), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($recipients as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $validator->errors()->add('recipients', sprintf(
                            lang('cupnoodles.contactforms::default.alert_invalid_recipient'), $email,
                        ));
                    }
                }

                $fields = (array)$this->input('fields', []);
                $hasEmailField = false;
                foreach ($fields as $index => $field) {
                    $type = $field['type'] ?? null;
                    $hasEmailField = $hasEmailField || $type === 'email';

                    if (in_array($type, ContactForm::OPTION_TYPES, true)
                        && !ContactForm::parseOptions((array)$field)
                    ) {
                        $validator->errors()->add('fields.'.$index.'.options', sprintf(
                            lang('cupnoodles.contactforms::default.alert_options_required'),
                            $field['label'] ?? '',
                        ));
                    }
                }

                if ($this->boolean('newsletter_opt_in') && !$hasEmailField) {
                    $validator->errors()->add('newsletter_opt_in', lang('cupnoodles.contactforms::default.alert_newsletter_needs_email'));
                }

                // Names left blank are generated (and de-duplicated) from the label when saving.
                $explicitNames = collect($fields)
                    ->map(fn($field): string => Str::snake(trim((string)($field['name'] ?? ''))))
                    ->filter();
                if ($explicitNames->duplicates()->isNotEmpty()) {
                    $validator->errors()->add('fields', lang('cupnoodles.contactforms::default.alert_duplicate_field_names'));
                }
            },
        ];
    }
}

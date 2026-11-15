<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Livewire;

use CupNoodles\ContactForms\Extension;
use CupNoodles\ContactForms\Models\ContactForm as ContactFormModel;
use CupNoodles\ContactForms\Models\Submission;
use Igniter\Admin\Classes\FormField;
use Igniter\Admin\Widgets\Form;
use Igniter\Frontend\Models\CaptchaSettings;
use Igniter\Frontend\Models\MailchimpSettings;
use Igniter\Frontend\Models\Subscriber;
use Igniter\Main\Traits\ConfigurableComponent;
use Igniter\System\Mail\AnonymousTemplateMailable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

final class ContactForm extends Component
{
    use ConfigurableComponent;

    public const string CODE = 'cupnoodles-contactforms::contact-form';

    /** Slug of the contact form to show, chosen in the page builder or passed as form="..." */
    #[Locked]
    public ?string $form = null;

    /** Submitted values keyed by field name. */
    public array $data = [];

    public bool $subscribe = false;

    public ?string $captchaToken = null;

    /** Honeypot: hidden from people, bots fill it in. */
    public string $website = '';

    public ?string $successMessage = null;

    public static function componentMeta(): array
    {
        return [
            'code' => self::CODE,
            'name' => 'cupnoodles.contactforms::default.component_title',
            'description' => 'cupnoodles.contactforms::default.component_desc',
        ];
    }

    public function defineProperties(): array
    {
        return [
            'form' => [
                'label' => 'lang:cupnoodles.contactforms::default.label_component_form',
                'type' => 'select',
                'placeholder' => 'lang:igniter::admin.text_please_select',
                'validationRule' => 'required|string|max:255',
            ],
        ];
    }

    public static function getPropertyOptions(Form $form, FormField $field): array|Collection
    {
        if ($field->fieldName === 'form') {
            return ContactFormModel::query()->orderBy('name')->pluck('name', 'slug');
        }

        return [];
    }

    public static function captchaIsConfigured(): bool
    {
        return CaptchaSettings::get('api_site_key') && CaptchaSettings::getApiSecretKey();
    }

    public function mount(): void
    {
        $this->resetData();
    }

    #[Computed]
    public function contactForm(): ?ContactFormModel
    {
        $form = ContactFormModel::findBySlug($this->form);

        return $form?->isEnabled() ? $form : null;
    }

    #[Computed]
    public function captchaSiteKey(): ?string
    {
        return $this->contactForm?->use_captcha && self::captchaIsConfigured()
            ? (string)CaptchaSettings::get('api_site_key')
            : null;
    }

    public function render(): View
    {
        return view('cupnoodles.contactforms::livewire.contact-form', [
            'contactForm' => $this->contactForm,
            'captchaSiteKey' => $this->captchaSiteKey,
            'captchaLang' => CaptchaSettings::getLang(),
        ]);
    }

    public function onSubmit(): void
    {
        $form = $this->contactForm;
        if (!$form) {
            return;
        }

        // Bots that fill the hidden field get the normal thank-you and nothing is sent.
        if ($this->website !== '') {
            $this->finish($form);

            return;
        }

        $this->ensureNotThrottled($form);

        $this->validate(
            $form->makeValidationRules(),
            [],
            $form->makeValidationAttributes(),
        );

        if ($this->captchaSiteKey) {
            $this->verifyCaptcha();
        }

        $values = array_intersect_key($this->data, array_flip(array_column($form->getFormFields(), 'name')));
        $emailField = $form->getEmailFieldName();
        $senderEmail = $emailField ? (($values[$emailField] ?? null) ?: null) : null;

        $subscribed = $this->subscribe && $senderEmail && $form->showsNewsletterOptIn()
            && $this->subscribeToNewsletter($senderEmail);

        $answers = Submission::buildData($form, $values);

        $submission = null;
        if ($form->store_submissions) {
            $submission = Submission::create([
                'contact_form_id' => $form->getKey(),
                'email' => $senderEmail,
                'data' => $answers,
                'ip_address' => request()->ip(),
                'newsletter_subscribed' => $subscribed,
            ]);
        }

        $this->sendNotification($form, $answers, $senderEmail);

        if ($form->throttle_limit > 0) {
            RateLimiter::hit($this->throttleKey($form), 3600);
        }

        Event::dispatch('cupnoodles.contactforms.submitted', [$form, $values, $submission]);

        $this->finish($form);
    }

    protected function finish(ContactFormModel $form): void
    {
        $this->resetData();
        $this->successMessage = $form->success_message ?: lang('cupnoodles.contactforms::default.alert_sent');
    }

    protected function resetData(): void
    {
        $this->data = [];
        foreach ($this->contactForm?->getFormFields() ?? [] as $field) {
            $this->data[$field['name']] = $field['type'] === 'checkbox' ? false : '';
        }

        $this->subscribe = false;
        $this->captchaToken = null;
        $this->website = '';
        $this->resetValidation();
    }

    protected function throttleKey(ContactFormModel $form): string
    {
        return 'cupnoodles-contactforms:'.$form->getKey().':'.request()->ip();
    }

    protected function ensureNotThrottled(ContactFormModel $form): void
    {
        if ($form->throttle_limit > 0 && RateLimiter::tooManyAttempts($this->throttleKey($form), $form->throttle_limit)) {
            $minutes = (int)ceil(RateLimiter::availableIn($this->throttleKey($form)) / 60);

            throw ValidationException::withMessages([
                'form' => sprintf(lang('cupnoodles.contactforms::default.alert_throttled'), max($minutes, 1)),
            ]);
        }
    }

    protected function verifyCaptcha(): void
    {
        // The `recaptcha` rule is registered by the igniter.frontend extension.
        $validator = Validator::make(
            ['captchaToken' => $this->captchaToken],
            ['captchaToken' => ['required', 'recaptcha']],
            ['captchaToken.required' => lang('cupnoodles.contactforms::default.alert_captcha_required')],
        );

        if ($validator->fails()) {
            // A token can only be checked once, so the visitor has to tick the box again.
            $this->captchaToken = null;
            $this->dispatch('contactform-captcha-reset', id: $this->getId());

            throw new ValidationException($validator);
        }
    }

    protected function subscribeToNewsletter(string $email): bool
    {
        try {
            $listId = MailchimpSettings::isConfigured() ? (MailchimpSettings::get('list_id') ?: null) : null;
            Subscriber::subscribe($email, $listId);

            return true;
        } catch (Throwable $e) {
            // A Mailchimp problem shouldn't lose the customer's message.
            Log::warning('Contact form newsletter sign-up failed: '.$e->getMessage());

            return false;
        }
    }

    protected function sendNotification(ContactFormModel $form, array $answers, ?string $senderEmail): void
    {
        $recipients = $form->listRecipients() ?: array_filter([setting('site_email')]);
        if (!$recipients) {
            Log::warning(sprintf('Contact form "%s" has no valid recipients, nothing was emailed.', $form->name));

            return;
        }

        $mailable = AnonymousTemplateMailable::create(Extension::MAIL_TEMPLATE)
            ->withSerializedData([
                'form_name' => $form->name,
                'subject' => $form->subject,
                'sender_email' => $senderEmail,
                'fields' => $answers,
            ])
            ->to($recipients);

        if ($senderEmail) {
            $mailable->replyTo($senderEmail);
        }

        Mail::queue($mailable);
    }
}

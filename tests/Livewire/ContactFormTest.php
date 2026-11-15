<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Tests\Livewire;

use CupNoodles\ContactForms\Livewire\ContactForm as Component;
use CupNoodles\ContactForms\Models\ContactForm;
use CupNoodles\ContactForms\Models\Submission;
use Igniter\Frontend\Classes\ReCaptcha;
use Igniter\Frontend\Models\CaptchaSettings;
use Igniter\Frontend\Models\Subscriber;
use Igniter\System\Mail\AnonymousTemplateMailable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function(): void {
    Mail::fake();

    $this->form = ContactForm::create([
        'name' => 'Catering',
        'slug' => 'catering',
        'recipients' => 'events@example.com, chef@example.com',
        'subject' => 'Catering request',
        'newsletter_opt_in' => true,
        'throttle_limit' => 2,
        'fields' => [
            ['label' => 'Name', 'type' => 'text', 'required' => true],
            ['label' => 'Email', 'type' => 'email', 'required' => true],
            ['label' => 'Guests', 'type' => 'select', 'options' => "1-10\n11-50"],
            ['label' => 'Message', 'type' => 'textarea'],
        ],
    ]);
});

function fillValid($component)
{
    return $component
        ->set('data.name', 'Alex')
        ->set('data.email', 'alex@example.com')
        ->set('data.guests', '11-50')
        ->set('data.message', "Line one\nLine two");
}

it('renders the configured fields', function(): void {
    Livewire::test(Component::class, ['form' => 'catering'])
        ->assertSeeHtml('wire:model="data.guests"')
        ->assertSee('11-50')
        ->assertSeeHtml('wire:model="subscribe"');
});

it('renders nothing for a missing or disabled form', function(): void {
    Livewire::test(Component::class, ['form' => 'missing'])->assertDontSeeHtml('<form');

    $this->form->update(['status' => false]);
    Livewire::test(Component::class, ['form' => 'catering'])->assertDontSeeHtml('<form');
});

it('validates submissions against the field definitions', function(): void {
    Livewire::test(Component::class, ['form' => 'catering'])
        ->set('data.email', 'not-an-email')
        ->set('data.guests', 'not-a-choice')
        ->call('onSubmit')
        ->assertHasErrors(['data.name' => 'required', 'data.email', 'data.guests']);

    Mail::assertNothingQueued();
});

it('emails, stores and subscribes a valid submission', function(): void {
    Event::fake(['cupnoodles.contactforms.submitted']);

    fillValid(Livewire::test(Component::class, ['form' => 'catering']))
        ->set('subscribe', true)
        ->call('onSubmit')
        ->assertHasNoErrors()
        ->assertSet('data.name', '')
        ->assertSee('Thank you');

    Mail::assertQueued(AnonymousTemplateMailable::class, fn($mail): bool => $mail->hasTo('events@example.com')
        && $mail->hasTo('chef@example.com')
        && $mail->hasReplyTo('alex@example.com'));

    $submission = Submission::sole();
    expect($submission->email)->toBe('alex@example.com')
        ->and($submission->newsletter_subscribed)->toBeTrue()
        ->and(collect($submission->data)->firstWhere('name', 'message')['value'])->toBe("Line one\nLine two")
        ->and(Subscriber::where('email', 'alex@example.com')->exists())->toBeTrue();

    Event::assertDispatched('cupnoodles.contactforms.submitted');
});

it('shows the custom thank-you message', function(): void {
    $this->form->update(['success_message' => 'See you soon!']);

    fillValid(Livewire::test(Component::class, ['form' => 'catering']))
        ->call('onSubmit')
        ->assertSet('successMessage', 'See you soon!');
});

it('does not store submissions when switched off', function(): void {
    $this->form->update(['store_submissions' => false]);

    fillValid(Livewire::test(Component::class, ['form' => 'catering']))->call('onSubmit');

    expect(Submission::count())->toBe(0);
    Mail::assertQueued(AnonymousTemplateMailable::class);
});

it('silently drops honeypot submissions', function(): void {
    fillValid(Livewire::test(Component::class, ['form' => 'catering']))
        ->set('website', 'http://spam.example')
        ->call('onSubmit')
        ->assertHasNoErrors()
        ->assertSee('Thank you');

    Mail::assertNothingQueued();
    expect(Submission::count())->toBe(0);
});

it('throttles repeated submissions from one visitor', function(): void {
    fillValid(Livewire::test(Component::class, ['form' => 'catering']))->call('onSubmit')->assertHasNoErrors();
    fillValid(Livewire::test(Component::class, ['form' => 'catering']))->call('onSubmit')->assertHasNoErrors();
    fillValid(Livewire::test(Component::class, ['form' => 'catering']))->call('onSubmit')->assertHasErrors(['form']);

    expect(Submission::count())->toBe(2);
});

it('requires a passing captcha when enabled', function(): void {
    CaptchaSettings::set(['api_site_key' => 'site-key', 'api_secret_key' => 'secret-key', 'lang' => 'en']);
    $this->form->update(['use_captcha' => true]);

    $recaptcha = mock(ReCaptcha::class);
    $recaptcha->shouldReceive('verifyResponse')->with('bad-token')->andReturnFalse();
    $recaptcha->shouldReceive('verifyResponse')->with('good-token')->andReturnTrue();
    app()->instance('recaptcha', $recaptcha);

    $component = fillValid(Livewire::test(Component::class, ['form' => 'catering']))
        ->assertSeeHtml('grecaptcha.render');

    $component->call('onSubmit')->assertHasErrors(['captchaToken' => 'required']);

    $component->set('captchaToken', 'bad-token')->call('onSubmit')
        ->assertHasErrors(['captchaToken'])
        ->assertDispatched('contactform-captcha-reset')
        ->assertSet('captchaToken', null);

    $component->set('captchaToken', 'good-token')->call('onSubmit')->assertHasNoErrors();

    expect(Submission::count())->toBe(1);
});

it('skips the captcha when keys are not configured', function(): void {
    $this->form->update(['use_captcha' => true]);

    fillValid(Livewire::test(Component::class, ['form' => 'catering']))
        ->assertDontSeeHtml('grecaptcha')
        ->call('onSubmit')
        ->assertHasNoErrors();
});

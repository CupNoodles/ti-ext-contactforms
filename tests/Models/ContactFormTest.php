<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Tests\Models;

use CupNoodles\ContactForms\Models\ContactForm;
use CupNoodles\ContactForms\Models\Submission;

it('generates the slug and unique field names on save', function(): void {
    $form = ContactForm::create([
        'name' => 'Catering Request',
        'recipients' => 'a@example.com',
        'subject' => 'Catering',
        'fields' => [
            ['label' => 'Your name', 'type' => 'text'],
            ['label' => 'Your name', 'type' => 'textarea'],
            ['label' => '', 'type' => 'text'],
            ['label' => 'Website', 'type' => 'text'],
            ['label' => '2nd choice', 'type' => 'bogus'],
        ],
    ]);

    expect($form->slug)->toBe('catering-request')
        ->and(array_column($form->getFormFields(), 'name'))->toBe(['your_name', 'your_name_2', 'website_2', 'field_2nd_choice'])
        ->and($form->getFormFields()[3]['type'])->toBe('text');
});

it('keeps an explicit field key', function(): void {
    $fields = ContactForm::normalizeFields([['label' => 'Email address', 'name' => 'email_address', 'type' => 'email']]);

    expect($fields[0]['name'])->toBe('email_address');
});

it('parses choices one per line', function(): void {
    expect(ContactForm::parseOptions(['options' => "Small\r\nLarge\n\n Small \nMedium"]))->toBe(['Small', 'Large', 'Medium']);
});

it('lists only valid recipients', function(): void {
    $form = new ContactForm(['recipients' => "one@example.com, nope;two@example.com\nthree@example.com one@example.com"]);

    expect($form->listRecipients())->toBe(['one@example.com', 'two@example.com', 'three@example.com']);
});

it('only offers newsletter sign-up when the form has an email field', function(): void {
    $form = new ContactForm(['newsletter_opt_in' => true, 'fields' => [['name' => 'name', 'type' => 'text']]]);
    expect($form->showsNewsletterOptIn())->toBeFalse();

    $form->fields = [['name' => 'email', 'type' => 'email']];
    expect($form->showsNewsletterOptIn())->toBeTrue()
        ->and($form->getEmailFieldName())->toBe('email');
});

it('builds validation rules from the fields', function(): void {
    $form = new ContactForm(['fields' => ContactForm::normalizeFields([
        ['label' => 'Email', 'type' => 'email', 'required' => true],
        ['label' => 'Size', 'type' => 'select', 'options' => "S\nM"],
        ['label' => 'Terms', 'type' => 'checkbox', 'required' => true],
    ])]);

    $rules = $form->makeValidationRules();

    expect($rules['data.email'])->toContain('required', 'email:filter')
        ->and($rules['data.size'][0])->toBe('nullable')
        ->and($rules['data.terms'])->toBe(['accepted'])
        ->and($form->makeValidationAttributes())->toBe(['data.email' => 'Email', 'data.size' => 'Size', 'data.terms' => 'Terms']);
});

it('deletes submissions with the form', function(): void {
    $form = ContactForm::create(['name' => 'Form', 'recipients' => 'a@example.com', 'subject' => 'S', 'fields' => [['label' => 'A']]]);
    Submission::create(['contact_form_id' => $form->getKey(), 'data' => []]);

    $form->delete();

    expect(Submission::count())->toBe(0);
});

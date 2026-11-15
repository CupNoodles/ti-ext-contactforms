<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Tests\Http\Requests;

use CupNoodles\ContactForms\Http\Requests\ContactFormRequest;
use Illuminate\Support\Facades\Validator;

function validateContactForm(array $data): array
{
    $request = ContactFormRequest::create('/', 'POST', $data);
    $request->setContainer(app());

    $validator = Validator::make($data, $request->rules(), [], $request->attributes());
    $validator->after($request->after());

    return $validator->errors()->toArray();
}

it('accepts a valid form', function(): void {
    expect(validateContactForm([
        'name' => 'Catering',
        'recipients' => 'a@example.com, b@example.com',
        'subject' => 'Catering request',
        'newsletter_opt_in' => true,
        'fields' => [['label' => 'Email', 'type' => 'email'], ['label' => 'Size', 'type' => 'radio', 'options' => "S\nM"]],
    ]))->toBe([]);
});

it('explains the mistakes that would break the form', function(): void {
    $errors = validateContactForm([
        'name' => 'Catering',
        'recipients' => 'not-an-email',
        'subject' => 'Catering request',
        'newsletter_opt_in' => true,
        'fields' => [
            ['label' => 'Size', 'type' => 'select', 'options' => ''],
            ['label' => 'A', 'name' => 'same', 'type' => 'text'],
            ['label' => 'B', 'name' => 'same', 'type' => 'text'],
        ],
    ]);

    expect($errors)->toHaveKeys(['recipients', 'fields.0.options', 'newsletter_opt_in', 'fields']);
});

it('requires at least one field', function(): void {
    expect(validateContactForm(['name' => 'X', 'recipients' => 'a@example.com', 'subject' => 'S', 'fields' => []]))
        ->toHaveKey('fields');
});

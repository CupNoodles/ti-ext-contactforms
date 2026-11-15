# Contact Forms for TastyIgniter

Build as many contact forms as you need (catering requests, event enquiries, feedback, job applications)
from the admin, with no HTML required. Each form emails the people you choose, keeps a copy of every
message in the admin, and can offer a newsletter sign-up.

TastyIgniter v4 ships with one fixed "Contact us" form that always goes to the site email address. Use
this extension when you need different questions, several forms, or messages routed to different people.

> Using TastyIgniter v3? Use the [`v3` branch](https://github.com/CupNoodles/ti-ext-contactforms/tree/v3). Note that the v3 version is much less feature-complete (explained in the v3 README).

## Features

- **Form builder in the admin**: add, reorder and remove fields. Field types are short text, email,
  phone number, number, date, time, long text, dropdown, multiple choice and checkbox. Each field can
  be required, half or full width, and have a placeholder and hint text.
- **Per-form recipients**: send each form to one or more addresses. Replies go straight to the visitor
  (Reply-To is set from the form's Email field).
- **Submissions inbox**: every message is saved under *Marketing > Form Submissions*, with filters by
  form and date and a "Reply by email" button. You can switch this off per form.
- **Spam protection**: a hidden honeypot field, a per-visitor hourly limit, and optional Google
  reCAPTCHA (v2 checkbox, using the keys from the FrontEnd extension's reCaptcha settings).
- **Newsletter opt-in**: an optional, unticked checkbox that adds the visitor to the FrontEnd
  extension's subscribers (and Mailchimp, if configured).
- **Clear feedback**: validation messages under each field, a "Sending…" state and a customisable
  thank-you message, all without a page reload.
- **Customisable email**: edit the *Contact form submission email to staff* template in
  *Design > Mail templates*. It lists whatever fields the form has.

## Requirements

- TastyIgniter v4.5 or later
- [FrontEnd extension](https://github.com/tastyigniter/ti-ext-frontend) (`igniter.frontend`, installed
  with TastyIgniter by default)

## Installation

### From GitHub (git clone)

From your TastyIgniter root folder, clone the extension into `extensions/cupnoodles/contactforms`. The
folder name matters: TastyIgniter finds extensions at `extensions/<vendor>/<name>`.

```bash
git clone https://github.com/CupNoodles/ti-ext-contactforms.git extensions/cupnoodles/contactforms
php artisan igniter:up
```

`igniter:up` creates the extension's database tables. To update later:

```bash
cd extensions/cupnoodles/contactforms
git pull
cd ../../..
php artisan igniter:up
```

### With Composer, from GitHub

If you manage TastyIgniter with Composer, you can install straight from the GitHub repository
without Packagist or the marketplace:

```bash
composer config repositories.cupnoodles-contactforms vcs https://github.com/CupNoodles/ti-ext-contactforms
composer require cupnoodles/ti-ext-contactforms:dev-master
php artisan igniter:up
```

Once a version is tagged, you can require `^4.0` instead of `dev-master`. Updates then come through
`composer update`.

## Usage

1. Go to **Marketing > Contact Forms** and click **New**.
2. Give the form a name, the address(es) to send to and an email subject.
3. On the **Fields** tab, add your questions. Include an *Email* field so you can reply.
4. On the **Settings** tab, set the button text, thank-you message, spam protection and newsletter
   options.
5. Add the form to your site:
   - **Page builder**: in *Design > Themes*, open the page builder, add a **Contact form** block to
     any page and choose your form.
   - **Theme template**: add the component tag where the form should appear:

     ```blade
     <livewire:cupnoodles-contactforms::contact-form form="catering-request" />
     ```

     `form` is the form's slug, shown on its edit screen.

The form uses standard Bootstrap 5 classes, so it matches the Orange theme and most Bootstrap themes
without extra CSS.

## For developers

After a submission is processed, the event `cupnoodles.contactforms.submitted` is dispatched with:

- the `ContactForm` model,
- an array of submitted values keyed by field key,
- the saved `Submission` model, or `null` if storing is switched off.

```php
Event::listen('cupnoodles.contactforms.submitted', function ($form, array $values, $submission) {
    if ($form->slug === 'catering-request') {
        // e.g. push to a CRM
    }
});
```

Field keys are generated from the field labels. Set them explicitly in the admin if your code
depends on them.

## Upgrading from v3

Run `php artisan igniter:up`. Existing forms are converted automatically:

- The fields v3 recognised (`first_name`, `last_name`, `email_address`, `phone_number`,
  `contact_message`, `date_requested`) become builder fields, in the same order, using the
  `<label for="…">` text from your HTML where it can be found.
- A `subscribe_to_newsletter` checkbox becomes the *Offer newsletter sign-up* setting.
- The "Email" setting becomes *Send to*.
- Your original HTML is kept in the `legacy_form` database column for reference. It is no longer
  rendered, so check each form's Fields tab after upgrading.
- The v3 `[contactForm]` component is replaced by the Livewire component and page builder block
  above. Update your theme templates.

## Testing

```bash
composer install
vendor/bin/pest
```

Tests use [sampoyigi/testbench](https://github.com/sampoyigi/testbench). Copy `phpunit.xml.dist` to
`phpunit.xml` and point it at an empty test database.

## License

MIT. See [LICENSE](LICENSE).

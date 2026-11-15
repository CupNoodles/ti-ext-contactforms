<?php

declare(strict_types=1);

use CupNoodles\ContactForms\Models\ContactForm;
use SamPoyigi\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

// Each test gets a fresh event dispatcher, but Flame remembers which models already had
// their beforeSave/afterSave hooks attached. Reset that so the hooks bind to the new one.
uses()->beforeEach(fn() => ContactForm::flushEventListeners())->in(__DIR__);

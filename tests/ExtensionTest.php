<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Tests;

use CupNoodles\ContactForms\Extension;
use CupNoodles\ContactForms\Livewire\ContactForm;
use Igniter\Main\Classes\BlockManager;
use Igniter\System\Classes\ComponentManager;

it('registers the storefront component', function(): void {
    expect(resolve(ComponentManager::class)->hasComponent(ContactForm::CODE))->toBeTrue();
});

it('registers a page builder block for every theme', function(): void {
    $blocks = (new Extension(app()))->registerBlocks();

    expect($blocks)->toHaveKey(BlockManager::ALL_THEMES)
        ->and($blocks[BlockManager::ALL_THEMES][0]->getComponentCode())->toBe(ContactForm::CODE);
});

it('registers permissions, navigation and mail template', function(): void {
    $extension = new Extension(app());

    expect($extension->registerPermissions())->toHaveKeys([
        'CupNoodles.ContactForms.ManageForms',
        'CupNoodles.ContactForms.ViewSubmissions',
    ])
        ->and($extension->registerNavigation()['marketing']['child'])->toHaveKeys(['contactforms', 'contactformsubmissions'])
        ->and($extension->registerMailTemplates())->toHaveKey(Extension::MAIL_TEMPLATE);
});

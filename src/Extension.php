<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms;

use CupNoodles\ContactForms\Livewire\ContactForm as ContactFormComponent;
use Igniter\Main\Classes\BlockManager;
use Igniter\Main\Classes\ComponentBlock;
use Igniter\System\Classes\BaseExtension;
use Illuminate\Support\Facades\Event;
use Override;

class Extension extends BaseExtension
{
    public const string MAIL_TEMPLATE = 'cupnoodles.contactforms::mail.contact_form';

    #[Override]
    public function boot(): void
    {
        // Sample values so the template preview in Design > Mail templates is readable.
        Event::listen('system.mail.sampleData', function($templateCode): ?array {
            if ($templateCode !== self::MAIL_TEMPLATE) {
                return null;
            }

            return [
                'form_name' => 'Catering request',
                'subject' => 'New catering request',
                'sender_email' => 'guest@example.com',
                'fields' => [
                    ['label' => 'Name', 'value' => 'Alex Guest'],
                    ['label' => 'Email', 'value' => 'guest@example.com'],
                    ['label' => 'Event date', 'value' => '2026-12-24'],
                    ['label' => 'Message', 'value' => "We'd like food for 40 people."],
                ],
            ];
        });
    }

    #[Override]
    public function registerComponents(): array
    {
        return [
            ContactFormComponent::class,
        ];
    }

    /**
     * Adds a "Contact form" block to the page builder of every theme.
     */
    public function registerBlocks(): array
    {
        return [
            BlockManager::ALL_THEMES => [
                ComponentBlock::make(ContactFormComponent::CODE, [
                    'icon' => 'fa fa-envelope-open-text',
                ]),
            ],
        ];
    }

    #[Override]
    public function registerNavigation(): array
    {
        return [
            'marketing' => [
                'child' => [
                    'contactforms' => [
                        'priority' => 20,
                        'class' => 'contactforms',
                        'href' => admin_url('cupnoodles/contactforms/contact_forms'),
                        'title' => lang('cupnoodles.contactforms::default.side_menu'),
                        'permission' => 'CupNoodles.ContactForms.ManageForms',
                    ],
                    'contactformsubmissions' => [
                        'priority' => 21,
                        'class' => 'contactformsubmissions',
                        'href' => admin_url('cupnoodles/contactforms/submissions'),
                        'title' => lang('cupnoodles.contactforms::default.side_menu_submissions'),
                        'permission' => 'CupNoodles.ContactForms.ViewSubmissions',
                    ],
                ],
            ],
        ];
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'CupNoodles.ContactForms.ManageForms' => [
                'description' => 'lang:cupnoodles.contactforms::default.permission_manage_forms',
                'group' => 'igniter::admin.permissions.name',
            ],
            'CupNoodles.ContactForms.ViewSubmissions' => [
                'description' => 'lang:cupnoodles.contactforms::default.permission_view_submissions',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }

    #[Override]
    public function registerMailTemplates(): array
    {
        return [
            self::MAIL_TEMPLATE => 'lang:cupnoodles.contactforms::default.text_mail_template',
        ];
    }
}

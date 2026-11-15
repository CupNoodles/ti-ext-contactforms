<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Http\Controllers;

use CupNoodles\ContactForms\Http\Requests\ContactFormRequest;
use CupNoodles\ContactForms\Models\ContactForm;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;

class ContactForms extends AdminController
{
    public array $implement = [
        ListController::class,
        FormController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => ContactForm::class,
            'title' => 'lang:cupnoodles.contactforms::default.text_title',
            'emptyMessage' => 'lang:cupnoodles.contactforms::default.text_empty',
            'defaultSort' => ['contact_form_id', 'DESC'],
            'configFile' => 'contactform',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:cupnoodles.contactforms::default.text_form_name',
        'model' => ContactForm::class,
        'request' => ContactFormRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'cupnoodles/contactforms/contact_forms/edit/{contact_form_id}',
            'redirectClose' => 'cupnoodles/contactforms/contact_forms',
            'redirectNew' => 'cupnoodles/contactforms/contact_forms/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'cupnoodles/contactforms/contact_forms/edit/{contact_form_id}',
            'redirectClose' => 'cupnoodles/contactforms/contact_forms',
            'redirectNew' => 'cupnoodles/contactforms/contact_forms/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'cupnoodles/contactforms/contact_forms',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/contactforms/contact_forms',
        ],
        'configFile' => 'contactform',
    ];

    protected null|string|array $requiredPermissions = 'CupNoodles.ContactForms.ManageForms';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('contactforms', 'marketing');
    }

    public function listExtendQuery($query): void
    {
        $query->withCount('submissions');
    }
}

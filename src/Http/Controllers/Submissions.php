<?php

declare(strict_types=1);

namespace CupNoodles\ContactForms\Http\Controllers;

use CupNoodles\ContactForms\Models\Submission;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;

/**
 * Read-only inbox of contact form submissions. With no 'create'/'edit' config,
 * FormController blocks those actions, so submissions can only be viewed and deleted.
 */
class Submissions extends AdminController
{
    public array $implement = [
        ListController::class,
        FormController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => Submission::class,
            'title' => 'lang:cupnoodles.contactforms::default.submissions.text_title',
            'emptyMessage' => 'lang:cupnoodles.contactforms::default.submissions.text_empty',
            'defaultSort' => ['created_at', 'DESC'],
            'configFile' => 'submission',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:cupnoodles.contactforms::default.submissions.text_form_name',
        'model' => Submission::class,
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'cupnoodles/contactforms/submissions',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/contactforms/submissions',
        ],
        'configFile' => 'submission',
    ];

    protected null|string|array $requiredPermissions = 'CupNoodles.ContactForms.ViewSubmissions';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('contactformsubmissions', 'marketing');
    }
}

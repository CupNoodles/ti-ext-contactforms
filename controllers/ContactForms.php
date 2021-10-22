<?php

namespace CupNoodles\ContactForms\Controllers;

class ContactForms extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController'
    ];

    public $listConfig = [
        'list' => [
            'model' => 'CupNoodles\ContactForms\Models\ContactForms',
            'title' => 'cupnoodles.contactforms::default.text_title',
            'emptyMessage' => 'cupnoodles.contactforms::default.text_empty',
            'defaultSort' => ['contact_forms_id', 'DESC'],
            'configFile' => 'contactforms_config',
        ],
    ];

    public $formConfig = [
        'name' => 'cupnoodles.contactforms::default.text_form_name',
        'model' => 'CupNoodles\ContactForms\Models\ContactForms',
        'request' => 'CupNoodles\ContactForms\Requests\ContactForms',
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'cupnoodles/contactforms/contactforms/edit/{contact_form_id}',
            'redirectClose' => 'cupnoodles/contactforms/contactforms',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'cupnoodles/contactforms/contactforms/edit/{contact_form_id}',
            'redirectClose' => 'cupnoodles/contactforms/contactforms',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'cupnoodles/contactforms/contactforms',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/contactforms/contactforms',
        ],
        'configFile' => 'contactforms_config',
    ];

}

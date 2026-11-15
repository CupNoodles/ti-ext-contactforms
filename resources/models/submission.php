<?php

use CupNoodles\ContactForms\Models\ContactForm;

$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:cupnoodles.contactforms::default.submissions.text_filter_search',
        'mode' => 'all',
    ],
    'scopes' => [
        'form' => [
            'label' => 'lang:cupnoodles.contactforms::default.submissions.text_filter_form',
            'type' => 'selectlist',
            'conditions' => 'contact_form_id IN(:filtered)',
            'modelClass' => ContactForm::class,
            'options' => 'getDropdownOptions',
        ],
        'date' => [
            'label' => 'lang:igniter::admin.text_filter_date',
            'type' => 'daterange',
            'conditions' => 'created_at >= CAST(:filtered_start AS DATE) AND created_at <= CAST(:filtered_end AS DATE)',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'forms' => [
            'label' => 'lang:cupnoodles.contactforms::default.button_forms',
            'class' => 'btn btn-default',
            'href' => 'cupnoodles/contactforms/contact_forms',
            'permission' => 'CupNoodles.ContactForms.ManageForms',
        ],
    ],
];

$config['list']['bulkActions'] = [
    'delete' => [
        'label' => 'lang:igniter::admin.button_delete',
        'class' => 'btn btn-light text-danger',
        'data-request-confirm' => 'lang:igniter::admin.alert_warning_confirm',
    ],
];

$config['list']['columns'] = [
    'preview' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-eye',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'cupnoodles/contactforms/submissions/preview/{submission_id}',
        ],
    ],
    'created_at' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_received',
        'type' => 'datetime',
    ],
    'form_name' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_form',
        'relation' => 'contact_form',
        'select' => 'name',
        'searchable' => true,
    ],
    'email' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_email',
        'type' => 'text',
        'searchable' => true,
    ],
    'summary' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_summary',
        'type' => 'text',
        'sortable' => false,
    ],
    'newsletter_subscribed' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_newsletter',
        'type' => 'switch',
        'invisible' => true,
    ],
    'ip_address' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_ip',
        'type' => 'text',
        'invisible' => true,
    ],
    'submission_id' => [
        'label' => 'lang:igniter::admin.column_id',
        'invisible' => true,
    ],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'back' => [
            'label' => 'lang:igniter::admin.button_icon_back',
            'class' => 'btn btn-outline-secondary',
            'href' => 'cupnoodles/contactforms/submissions',
        ],
    ],
];

$config['form']['fields'] = [
    'form_name' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_form',
        'type' => 'text',
        'span' => 'left',
        'disabled' => true,
    ],
    'created_at' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_received',
        'type' => 'datepicker',
        'mode' => 'datetime',
        'span' => 'right',
        'disabled' => true,
    ],
    'data' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.label_answers',
        'type' => 'partial',
        'path' => 'cupnoodles.contactforms::submissions.data',
    ],
    'email' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_email',
        'type' => 'text',
        'span' => 'left',
        'disabled' => true,
    ],
    'ip_address' => [
        'label' => 'lang:cupnoodles.contactforms::default.submissions.column_ip',
        'type' => 'text',
        'span' => 'right',
        'disabled' => true,
    ],
];

return $config;

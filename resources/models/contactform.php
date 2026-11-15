<?php

use CupNoodles\ContactForms\Models\ContactForm;

$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:cupnoodles.contactforms::default.text_filter_search',
        'mode' => 'all',
    ],
    'scopes' => [
        'status' => [
            'label' => 'lang:igniter::admin.text_filter_status',
            'type' => 'switch',
            'conditions' => 'status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:igniter::admin.button_new',
            'class' => 'btn btn-primary',
            'href' => 'cupnoodles/contactforms/contact_forms/create',
        ],
        'submissions' => [
            'label' => 'lang:cupnoodles.contactforms::default.button_submissions',
            'class' => 'btn btn-default',
            'href' => 'cupnoodles/contactforms/submissions',
            'permission' => 'CupNoodles.ContactForms.ViewSubmissions',
        ],
    ],
];

$config['list']['bulkActions'] = [
    'status' => [
        'label' => 'lang:igniter::admin.list.actions.label_status',
        'type' => 'dropdown',
        'class' => 'btn btn-light',
        'statusColumn' => 'status',
        'menuItems' => [
            'enable' => [
                'label' => 'lang:igniter::admin.list.actions.label_enable',
                'type' => 'button',
                'class' => 'dropdown-item',
            ],
            'disable' => [
                'label' => 'lang:igniter::admin.list.actions.label_disable',
                'type' => 'button',
                'class' => 'dropdown-item text-danger',
            ],
        ],
    ],
    'delete' => [
        'label' => 'lang:igniter::admin.button_delete',
        'class' => 'btn btn-light text-danger',
        'data-request-confirm' => 'lang:cupnoodles.contactforms::default.alert_delete_confirm',
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'cupnoodles/contactforms/contact_forms/edit/{contact_form_id}',
        ],
    ],
    'name' => [
        'label' => 'lang:cupnoodles.contactforms::default.label_name',
        'type' => 'text',
        'searchable' => true,
    ],
    'slug' => [
        'label' => 'lang:cupnoodles.contactforms::default.label_slug',
        'type' => 'text',
        'searchable' => true,
    ],
    'recipients' => [
        'label' => 'lang:cupnoodles.contactforms::default.label_recipients',
        'type' => 'text',
        'searchable' => true,
    ],
    'submissions_count' => [
        'label' => 'lang:cupnoodles.contactforms::default.column_submissions',
        'type' => 'number',
        'sortable' => false,
    ],
    'status' => [
        'label' => 'lang:igniter::admin.label_status',
        'type' => 'switch',
    ],
    'updated_at' => [
        'label' => 'lang:igniter::admin.column_date_updated',
        'type' => 'timetense',
        'invisible' => true,
    ],
    'contact_form_id' => [
        'label' => 'lang:igniter::admin.column_id',
        'invisible' => true,
    ],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:igniter::admin.button_save',
            'context' => ['create', 'edit'],
            'partial' => 'form/toolbar_save_button',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'igniter::admin.text_saving',
        ],
        'submissions' => [
            'label' => 'lang:cupnoodles.contactforms::default.button_submissions',
            'context' => ['edit'],
            'class' => 'btn btn-default',
            'href' => 'cupnoodles/contactforms/submissions',
            'permission' => 'CupNoodles.ContactForms.ViewSubmissions',
        ],
        'delete' => [
            'label' => 'lang:igniter::admin.button_icon_delete',
            'context' => ['edit'],
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-submit' => 'true',
            'data-request-confirm' => 'lang:cupnoodles.contactforms::default.alert_delete_confirm',
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:cupnoodles.contactforms::default.text_tab_general',
    'fields' => [
        // General
        'name' => [
            'label' => 'lang:cupnoodles.contactforms::default.label_name',
            'type' => 'text',
            'span' => 'left',
            'comment' => 'lang:cupnoodles.contactforms::default.help_name',
        ],
        'slug' => [
            'label' => 'lang:cupnoodles.contactforms::default.label_slug',
            'type' => 'text',
            'span' => 'right',
            'cssClass' => 'flex-width',
            'comment' => 'lang:cupnoodles.contactforms::default.help_slug',
        ],
        'status' => [
            'label' => 'lang:igniter::admin.label_status',
            'type' => 'switch',
            'span' => 'right',
            'cssClass' => 'flex-width',
            'default' => true,
        ],
        'recipients' => [
            'label' => 'lang:cupnoodles.contactforms::default.label_recipients',
            'type' => 'text',
            'span' => 'left',
            'comment' => 'lang:cupnoodles.contactforms::default.help_recipients',
            'attributes' => ['maxlength' => 1000],
        ],
        'subject' => [
            'label' => 'lang:cupnoodles.contactforms::default.label_subject',
            'type' => 'text',
            'span' => 'right',
            'comment' => 'lang:cupnoodles.contactforms::default.help_subject',
        ],
        'usage' => [
            'type' => 'partial',
            'path' => 'cupnoodles.contactforms::contactforms.usage',
            'context' => ['edit', 'preview'],
        ],

        // Fields
        'fields' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_fields',
            'type' => 'repeater',
            'sortable' => true,
            'prompt' => 'lang:cupnoodles.contactforms::default.button_add_field',
            'commentAbove' => 'lang:cupnoodles.contactforms::default.help_fields',
            'form' => [
                'fields' => [
                    'priority' => [
                        'type' => 'hidden',
                    ],
                    'label' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_label',
                        'type' => 'text',
                    ],
                    'type' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_type',
                        'type' => 'select',
                        'default' => 'text',
                        'options' => [ContactForm::class, 'getFieldTypeOptions'],
                    ],
                    'required' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_required',
                        'type' => 'switch',
                        'default' => false,
                    ],
                    'width' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_width',
                        'type' => 'radiotoggle',
                        'default' => 'full',
                        'options' => [
                            'full' => 'lang:cupnoodles.contactforms::default.text_width_full',
                            'half' => 'lang:cupnoodles.contactforms::default.text_width_half',
                        ],
                    ],
                    'options' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_options',
                        'type' => 'textarea',
                        'attributes' => ['rows' => 3],
                        'comment' => 'lang:cupnoodles.contactforms::default.help_field_options',
                        'trigger' => [
                            'action' => 'show',
                            'field' => 'type',
                            'condition' => 'value[select][radio]',
                        ],
                    ],
                    'placeholder' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_placeholder',
                        'type' => 'text',
                    ],
                    'help' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_help',
                        'type' => 'text',
                    ],
                    'name' => [
                        'label' => 'lang:cupnoodles.contactforms::default.label_field_name',
                        'type' => 'text',
                        'comment' => 'lang:cupnoodles.contactforms::default.help_field_name',
                    ],
                ],
            ],
        ],

        // After submitting
        'submit_label' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_submit_label',
            'type' => 'text',
            'span' => 'left',
            'placeholder' => 'lang:cupnoodles.contactforms::default.button_send',
        ],
        'success_message' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_success_message',
            'type' => 'textarea',
            'span' => 'left',
            'attributes' => ['rows' => 3],
            'placeholder' => 'lang:cupnoodles.contactforms::default.alert_sent',
        ],
        'store_submissions' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_store_submissions',
            'type' => 'switch',
            'span' => 'right',
            'default' => true,
            'comment' => 'lang:cupnoodles.contactforms::default.help_store_submissions',
        ],

        // Newsletter
        'newsletter_opt_in' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_newsletter_opt_in',
            'type' => 'switch',
            'span' => 'left',
            'comment' => 'lang:cupnoodles.contactforms::default.help_newsletter_opt_in',
        ],
        'newsletter_label' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_newsletter_label',
            'type' => 'text',
            'span' => 'right',
            'placeholder' => 'lang:cupnoodles.contactforms::default.text_newsletter_default',
            'trigger' => [
                'action' => 'show',
                'field' => 'newsletter_opt_in',
                'condition' => 'checked',
            ],
        ],

        // Spam protection
        'use_captcha' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_use_captcha',
            'type' => 'switch',
            'span' => 'left',
            'comment' => 'lang:cupnoodles.contactforms::default.help_use_captcha',
        ],
        'throttle_limit' => [
            'tab' => 'lang:cupnoodles.contactforms::default.text_tab_settings',
            'label' => 'lang:cupnoodles.contactforms::default.label_throttle_limit',
            'type' => 'number',
            'span' => 'right',
            'default' => 5,
            'comment' => 'lang:cupnoodles.contactforms::default.help_throttle_limit',
        ],
    ],
];

return $config;

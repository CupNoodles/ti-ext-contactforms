<?php

namespace CupNoodles\ContactForms\Requests;

use System\Classes\FormRequest;

class ContactForms extends FormRequest
{
    public function rules()
    {
        return [
            ['name', 'cupnoodles.contactforms::default.name', 'required|between:2,128'],
            ['email', 'cupnoodles.contactforms::default.email', 'required'],
            ['subject', 'cupnoodles.contactforms::default.subject', 'required'],

        ];
    }

}

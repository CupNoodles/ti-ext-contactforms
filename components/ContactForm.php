<?php

namespace CupNoodles\ContactForms\Components;

use CupNoodles\ContactForms\Models\ContactForms;

use System\Classes\BaseComponent;

use System\Traits\SendsMailTemplate;
use Redirect;

class ContactForm extends BaseComponent
{

    use SendsMailTemplate;

    protected $contactForm;

    public function defineProperties()
    {
        return [
            'slug' => [
                'label' => 'igniter.pages::default.slug',
                'default' => '{{ :slug }}',
                'type' => 'text',
                'validationRule' => 'required|string',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['contact_form_'.$this->param('slug', $this->property('slug'))] = $this->contactForm = $this->createForm();
    }

    public function onRender()
    {
        $this->addJs('js/contact-form.js', 'contact-form');
    }

    public function slug(){
       return $this->param('slug', $this->property('slug'));
    }
    public function form_code()
    {
        return $this->contactForm;
    }

    public function createForm()
    {
        $slug = $this->param('slug', $this->property('slug'));
        $model = ContactForms::where('slug', $slug)->first();

        $form = $this->param('form');
        $form .= $model->form;
        return $form;
    }

    public function onContactFormSubmit(){

        $slug = $this->param('slug', $this->property('slug'));

        $this->mailSend('cupnoodles.contactforms::mail.contact_form', 'admin');

        flash()->success(
            lang('cupnoodles.contactforms::default.email_sent')
        );

        return Redirect::to('/');
    }

    public function mailGetRecipients($type)
    {
        $slug = $this->param('slug', $this->property('slug'));
        $model = ContactForms::where('slug', $slug)->first();

        $recipients = [];

        $recipients[] = [$model->email, setting('site_name')];

        return $recipients;
    }

    public function mailGetData()
    {
        $data = [];

        $slug = $this->param('slug', $this->property('slug'));
        $model = ContactForms::where('slug', $slug)->first();


        // TOOD this should be an editable field in each custom contact form.
        $filter_keys = ['first_name', 'last_name', 'email_address', 'contact_message', 'date_requested'];
        $postdata = post();
        foreach($postdata as $key=>$value){
            if(in_array($key, $filter_keys)){
                $data[$key] = $value;
            }
        }

        $data['subject'] = $model->subject;

        return $data;

    }


}

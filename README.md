## Contact Forms Extension

HTML-based Contact forms for TastyIgniter. This extension does not come with a Form Builder or editor, and rather relies on each user creating an html template for each contact form - if you're looking for a form builder, there's a paid extension at https://tastyigniter.com/marketplace/item/concreta-formbuilder which may suit your needs much better.


## Installation

Copy/clone this repository into `extensions/cupnoodles/contactforms`.

## Usage

Create a new contact form and fill it with form fields that you'd like to have sent to the emali address set on the contact form. 

The following variables are valid within a contact form:

```
'first_name'
'last_name'
'email_address'
'contact_message'
'date_requested'
'phone_number'
'subscribe_to_newsletter'
```

At a further date, these should be made editable within the contact form admin. 

An example html contact form may look like

```
<div class="row">
    <div class="col-6">
        <label for="sc-first-name">First Name</label>
        <input id="sc-first-name" type="text" class="form-control" name="first_name" />
    </div>
    <div class="col-6">
        <label for="sc-last-name">Last Name</label>
        <input id="sc-last-name" type="text" class="form-control" name="last_name" />
    </div>
</div>
<div class="row">
    <div class="col-12">
        <label for="sc-email-adress">Email Address</label>
        <input id="sc-email-address" type="text" class="form-control" name="email_address" />
    </div>
</div>
<div class="row">
	<div class="col-12">
    	<input type="checkbox" name="subscribe_to_newsletter" id="subscribe_to_newsletter" class="form-control" value="1" />
        <label for="subscribe_to_newsletter">Subscribe to our newsletter (opt-out at any time)</label>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <label for="sc-phone">Phone Number</label>
        <input id="sc-phone" type="text" class="form-control" name="phone_number" />
    </div>
</div>
<div class="row">
    <div class="col-12">
        <label for="sc-contact-message">Message</label>
        <textarea id="sc-contact-message" class="form-control" name="contact_message" ></textarea>
    </div>
</div>
<div class="row">
    <div class="col-12 col-sm-6">
        <label for="sc-date-requested">Requested Date</label>
        <input id="sc-date-requested" class="form-control" type="date" name="date_requested" />
    </div>
</div>
```

Then include the component into your template with 

```
@component('my_contact_form_slug')
```
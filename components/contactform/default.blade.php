<form id="contact_form_<?= $__SELF__->slug() ?>" class="contact-form" method="POST" data-request="<?= $__SELF__->alias ?>::onContactFormSubmit">
    <input type="hidden" name="contact_form_slug" value="<?= $__SELF__->slug() ?>" />
    <?= $__SELF__->form_code(); ?>
</form>

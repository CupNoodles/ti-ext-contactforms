@php($slug = $formModel->slug)
<div class="form-group span-full">
    <div class="bg-light p-3 rounded border">
        <h5 class="mb-2"><i class="fa fa-circle-info"></i>&nbsp;@lang('cupnoodles.contactforms::default.usage.title')</h5>
        <p class="mb-2">@lang('cupnoodles.contactforms::default.usage.builder')</p>
        <p class="mb-1">@lang('cupnoodles.contactforms::default.usage.blade')</p>
        <pre class="bg-white border rounded p-2 mb-2"><code>&lt;livewire:cupnoodles-contactforms::contact-form form="{{ $slug }}" /&gt;</code></pre>
        @if(!$formModel->status)
            <p class="text-danger mb-0">@lang('cupnoodles.contactforms::default.usage.disabled')</p>
        @endif
        @if($formModel->use_captcha && !\CupNoodles\ContactForms\Livewire\ContactForm::captchaIsConfigured())
            <p class="text-warning mb-0">@lang('cupnoodles.contactforms::default.usage.captcha_missing')</p>
        @endif
    </div>
</div>

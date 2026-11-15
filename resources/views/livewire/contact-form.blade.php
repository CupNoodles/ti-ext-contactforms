<div class="contact-form" data-contact-form="{{ $form }}">
    @if($contactForm)
        @if($successMessage)
            <div class="alert alert-success mb-0" role="status">{!! nl2br(e($successMessage)) !!}</div>
        @else
            <form wire:submit="onSubmit" novalidate>
                @error('form')
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="row g-3">
                    @foreach($contactForm->getFormFields() as $field)
                        @php
                            $id = 'cf-'.$this->getId().'-'.$field['name'];
                            $key = 'data.'.$field['name'];
                            $invalid = $errors->has($key);
                        @endphp
                        <div @class(['col-12', 'col-md-6' => $field['width'] === 'half']) wire:key="{{ $id }}">
                            @if($field['type'] === 'checkbox')
                                <div class="form-check">
                                    <input
                                        id="{{ $id }}"
                                        type="checkbox"
                                        wire:model="{{ $key }}"
                                        @class(['form-check-input', 'is-invalid' => $invalid])
                                        @if($field['required']) required aria-required="true" @endif
                                    />
                                    <label class="form-check-label" for="{{ $id }}">
                                        {{ $field['label'] }}@if($field['required'])<span class="text-danger" aria-hidden="true"> *</span>@endif
                                    </label>
                                    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            @elseif($field['type'] === 'radio')
                                <fieldset>
                                    <legend class="form-label fs-6 mb-1">
                                        {{ $field['label'] }}@if($field['required'])<span class="text-danger" aria-hidden="true"> *</span>@endif
                                    </legend>
                                    @foreach(\CupNoodles\ContactForms\Models\ContactForm::parseOptions($field) as $index => $option)
                                        <div class="form-check">
                                            <input
                                                id="{{ $id }}-{{ $index }}"
                                                type="radio"
                                                value="{{ $option }}"
                                                wire:model="{{ $key }}"
                                                @class(['form-check-input', 'is-invalid' => $invalid])
                                            />
                                            <label class="form-check-label" for="{{ $id }}-{{ $index }}">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </fieldset>
                            @else
                                <label class="form-label" for="{{ $id }}">
                                    {{ $field['label'] }}@if($field['required'])<span class="text-danger" aria-hidden="true"> *</span>@endif
                                </label>
                                @if($field['type'] === 'textarea')
                                    <textarea
                                        id="{{ $id }}"
                                        rows="5"
                                        wire:model="{{ $key }}"
                                        placeholder="{{ $field['placeholder'] }}"
                                        @class(['form-control', 'is-invalid' => $invalid])
                                        @if($field['required']) required aria-required="true" @endif
                                    ></textarea>
                                @elseif($field['type'] === 'select')
                                    <select
                                        id="{{ $id }}"
                                        wire:model="{{ $key }}"
                                        @class(['form-select', 'is-invalid' => $invalid])
                                        @if($field['required']) required aria-required="true" @endif
                                    >
                                        <option value="">{{ $field['placeholder'] ?: lang('cupnoodles.contactforms::default.text_choose') }}</option>
                                        @foreach(\CupNoodles\ContactForms\Models\ContactForm::parseOptions($field) as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        id="{{ $id }}"
                                        type="{{ $field['type'] }}"
                                        wire:model="{{ $key }}"
                                        placeholder="{{ $field['placeholder'] }}"
                                        @if($field['type'] === 'email') autocomplete="email" @endif
                                        @if($field['type'] === 'tel') autocomplete="tel" @endif
                                        @class(['form-control', 'is-invalid' => $invalid])
                                        @if($field['required']) required aria-required="true" @endif
                                    />
                                @endif
                                @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                            @if($field['help'])
                                <div class="form-text">{{ $field['help'] }}</div>
                            @endif
                        </div>
                    @endforeach

                    @if($contactForm->showsNewsletterOptIn())
                        <div class="col-12">
                            <div class="form-check">
                                <input id="cf-{{ $this->getId() }}-subscribe" type="checkbox" class="form-check-input" wire:model="subscribe"/>
                                <label class="form-check-label" for="cf-{{ $this->getId() }}-subscribe">
                                    {{ $contactForm->newsletter_label ?: lang('cupnoodles.contactforms::default.text_newsletter_default') }}
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Honeypot, hidden from people and screen readers --}}
                    <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
                        <label for="cf-{{ $this->getId() }}-website">Website</label>
                        <input id="cf-{{ $this->getId() }}-website" type="text" wire:model="website" tabindex="-1" autocomplete="off"/>
                    </div>

                    @if($captchaSiteKey)
                        <div class="col-12">
                            <div
                                wire:ignore
                                x-data="{
                                    widgetId: null,
                                    init() {
                                        const render = () => {
                                            this.widgetId = grecaptcha.render(this.$refs.box, {
                                                sitekey: @js($captchaSiteKey),
                                                callback: (token) => $wire.set('captchaToken', token, false),
                                                'expired-callback': () => $wire.set('captchaToken', null, false),
                                            });
                                        };
                                        if (window.grecaptcha && window.grecaptcha.render) {
                                            render();
                                        } else {
                                            (window.__contactFormCaptchaQueue ??= []).push(render);
                                        }
                                    },
                                }"
                                x-on:contactform-captcha-reset.window="if ($event.detail.id === $wire.id && widgetId !== null) grecaptcha.reset(widgetId)"
                            >
                                <div x-ref="box"></div>
                            </div>
                            @error('captchaToken')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="onSubmit">
                            <span wire:loading.remove wire:target="onSubmit">{{ $contactForm->submit_label ?: lang('cupnoodles.contactforms::default.button_send') }}</span>
                            <span wire:loading wire:target="onSubmit">@lang('cupnoodles.contactforms::default.text_sending')</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    @endif

    @if($captchaSiteKey && !$successMessage)
        @once
            <script>
                window.contactFormCaptchaLoaded = function () {
                    (window.__contactFormCaptchaQueue || []).forEach((render) => render());
                    window.__contactFormCaptchaQueue = [];
                };
            </script>
            <script src="https://www.google.com/recaptcha/api.js?onload=contactFormCaptchaLoaded&render=explicit&hl={{ urlencode($captchaLang) }}" async defer></script>
        @endonce
    @endif
</div>

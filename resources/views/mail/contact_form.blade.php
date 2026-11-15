subject = "{{ $subject }}"
==
New message from the "{{ $form_name }}" form on {{ $site_name }}.

@foreach($fields as $field)
{{ $field['label'] }}:
{{ $field['value'] !== '' ? $field['value'] : '-' }}

@endforeach
@if($sender_email)
Reply to this email to answer {{ $sender_email }} directly.
@endif
==
## {{ $subject }}

New message from the **{{ $form_name }}** form on {{ $site_name }}.

@foreach($fields as $field)
<div><strong>{{ $field['label'] }}</strong><br>{!! $field['value'] !== '' ? str_replace(["\r\n", "\r", "\n"], '<br>', e($field['value'])) : '&mdash;' !!}</div>

@endforeach
@if($sender_email)
<br>Reply to this email to answer {{ $sender_email }} directly.
@endif

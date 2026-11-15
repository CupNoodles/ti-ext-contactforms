<div class="form-group span-full">
    <div class="table-responsive border rounded">
        <table class="table mb-0">
            <tbody>
            @forelse((array)$formModel->data as $answer)
                <tr>
                    <th class="w-25 text-nowrap align-top">{{ $answer['label'] ?? $answer['name'] ?? '' }}</th>
                    <td style="white-space: pre-wrap">{{ ($answer['value'] ?? '') !== '' ? $answer['value'] : '—' }}</td>
                </tr>
            @empty
                <tr><td class="text-muted">@lang('cupnoodles.contactforms::default.submissions.text_no_answers')</td></tr>
            @endforelse
            @if($formModel->newsletter_subscribed)
                <tr>
                    <th class="w-25 text-nowrap">@lang('cupnoodles.contactforms::default.submissions.column_newsletter')</th>
                    <td>@lang('cupnoodles.contactforms::default.text_yes')</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
    @if($formModel->email)
        <a
            class="btn btn-outline-primary mt-3"
            href="mailto:{{ $formModel->email }}?subject={{ rawurlencode('Re: '.($formModel->contact_form?->subject ?? '')) }}"
        ><i class="fa fa-reply"></i>&nbsp;&nbsp;@lang('cupnoodles.contactforms::default.submissions.button_reply')</a>
    @endif
</div>

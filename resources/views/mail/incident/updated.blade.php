@component('mail::message')
# {{ $incident->name }}

@if($resolved)
{{ __('cachet::notification.incident.resolved.intro') }}
@else
{{ __('cachet::notification.incident.updated.intro') }}
@endif

@if($update->status)
**{{ __('cachet::notification.incident.status') }}:** {{ $update->status->getLabel() }}
@endif

@if($update->message)
{{ $update->message }}
@endif

@component('mail::button', ['url' => $incidentUrl])
{{ __('cachet::notification.incident.view_button') }}
@endcomponent

{{ __('cachet::notification.thanks') }}<br>
{{ config('app.name') }}

<small>{{ __('cachet::notification.unsubscribe.text') }} [{{ __('cachet::notification.unsubscribe.link') }}]({{ $manageUrl }})</small>
@endcomponent

@component('mail::message')
# {{ $incident->name }}

{{ __('cachet::notification.incident.created.intro') }}

@if($incident->status)
**{{ __('cachet::notification.incident.status') }}:** {{ $incident->status->getLabel() }}
@endif

@if($incident->message)
{{ $incident->message }}
@endif

@component('mail::button', ['url' => $incidentUrl])
{{ __('cachet::notification.incident.view_button') }}
@endcomponent

{{ __('cachet::notification.thanks') }}<br>
{{ config('app.name') }}

<small>{{ __('cachet::notification.unsubscribe.text') }} [{{ __('cachet::notification.unsubscribe.link') }}]({{ $manageUrl }})</small>
@endcomponent

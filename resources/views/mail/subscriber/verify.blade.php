@component('mail::message')
# {{ __('cachet::notification.subscriber.verify.heading') }}

{{ __('cachet::notification.subscriber.verify.body') }}

@component('mail::button', ['url' => $verifyUrl])
{{ __('cachet::notification.subscriber.verify.button') }}
@endcomponent

{{ __('cachet::notification.subscriber.verify.ignore') }}

{{ __('cachet::notification.thanks') }}<br>
{{ config('app.name') }}
@endcomponent

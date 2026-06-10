<x-mail::message>
# {{ $greeting ?: 'Justice Hub Notification' }}

{{ $body }}

@if($actionText && $actionUrl)
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endif

Thanks,<br>
**{{ config('app.name') }}**<br>
Legal Aid Society
</x-mail::message>

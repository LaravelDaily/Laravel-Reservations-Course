<x-mail::message>
# You Have Been Invited

You have been invited to the {{ config('app.name') }}

<x-mail::button :url="$inviteUrl">
Register
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

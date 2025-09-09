@component('mail::message')
# ¡Hola, {{ $user->name }}!

Tu registro fue exitoso. Ya puedes autenticarte y usar la API.

@component('mail::button', ['url' => config('app.url')])
Ir a la App
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
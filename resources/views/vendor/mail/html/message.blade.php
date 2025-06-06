<?php

declare(strict_types=1);

?>
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="Config::get('app.url')">
{{ Config::get('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
 @endisset

 {{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{!! __('email.automated_notification', ['app_name' => Config::get('app.name'), 'github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier']) !!}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
<?php

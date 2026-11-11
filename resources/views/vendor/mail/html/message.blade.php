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

 {{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
@isset($subcopy)
<div style="padding-bottom:16px;">
{{ $subcopy }}
</div>
@endisset
<div style="color:#A1A1AA;font-size:12px;line-height:18px;">
{!! str_replace('<a ', '<a style="color:#A1A1AA;text-decoration:underline;" ', __('email.automated_notification', ['app_name' => Config::get('app.name'), 'github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier'])) !!}
</div>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
<?php

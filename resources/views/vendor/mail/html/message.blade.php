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
Automated notification from the <a href="https://github.com/lewislarsen/youtube-channel-notifier">{{ Config::get('app.name') }}</a> Project.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
<?php

<?php

declare(strict_types=1);

?>
<x-mail::message>
## {{ __('email.new_upload_from', ['creator' => $videoCreator]) }}

{{ __('email.creator_uploaded_video', ['creator' => $videoCreator]) }}

<div style="text-align: center; margin: 25px 0;">
<a href="{{ $videoUrl }}" style="display: inline-block;">
<img src="{{ $thumbnailUrl }}" alt="{{ $videoTitle }}" style="max-width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
</a>
</div>

## {{ $videoTitle }}

<p style="text-align: center; color: #6b7280; font-size: 14px; margin-bottom: -10px;margin-top:-10px;">
{{ __('email.published_date', ['date' => $published]) }}
</p>

<x-mail::button :url="$videoUrl" color="primary">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height:20px; width:20px; margin-bottom:-5px;">
<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
</svg>
{{ __('email.watch_on_youtube') }}
</x-mail::button>

<p style="text-align: center; color: #6b7280; font-size: 14px;">
{!! __('email.alternative_watch_link', ['link' => $videoUrl]) !!}
</p>

<x-mail::subcopy>
{!! __('email.notification_reason', ['creator' => $videoCreator]) !!} {!! __('email.documentation_link', ['github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier?tab=readme-ov-file#youtube-channel-notifier-ycn-project']) !!}
</x-mail::subcopy>
</x-mail::message>
<?php

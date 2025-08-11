<?php

declare(strict_types=1);

?>
<x-mail::message>
# {{ __('email.weekly_summary_subject') }}

{{ __('email.weekly_summary_intro') }}

@foreach($videos as $video)
<div style="margin-bottom: 30px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #f9fafb;">
<div style="display: flex; align-items: center; margin-bottom: 15px;">
<div style="flex: 1;">
<h3 style="margin: 0 0 5px 0; font-size: 18px; color: #1f2937;">
 <a href="{{ $video->getYoutubeUrl() }}" style="text-decoration: none; color: #1f2937;">
{{ $video->title }}
</a>
</h3>
<p style="margin: 0; color: #6b7280; font-size: 14px;">
@if(isset($video->channel))
{!! __('email.by_creator', ['creator' => '<a href="' . $video->channel->getChannelUrl() . '" style="color: #dc2626; text-decoration: none;">' . $video->channel->name . '</a>']) !!}
• {{ $video->getFormattedPublishedDate() }}
 @endif
</p>
</div>
</div>

<div style="text-align: center; margin: 15px 0;">
<a href="{{ $video->getYoutubeUrl() }}" style="display: inline-block;">
<img src="{{ $video->getThumbnailUrl('maxresdefault') }}" alt="{{ $video->title }}" style="max-width: 100%; max-height: 200px; border-radius: 6px; border: 1px solid #d1d5db; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
</a>
</div>

<div style="text-align: center;">
<x-mail::button :url="$video->getYoutubeUrl()" color="primary" style="display: inline-block; margin: 0 auto;">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height:16px; width:16px; margin-right: 5px; vertical-align: middle;">
<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
</svg>
{{ __('email.watch_on_youtube') }}
</x-mail::button>
</div>
</div>
@endforeach
<x-mail::subcopy>
{!! __('email.weekly_summary_notification_reason') !!} {!! __('email.documentation_link', ['github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier?tab=readme-ov-file#youtube-channel-notifier-ycn-project']) !!}
</x-mail::subcopy>
</x-mail::message>

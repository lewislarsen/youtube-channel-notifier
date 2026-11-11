<?php

declare(strict_types=1);

?>
<x-mail::message>
# {{ __('email.weekly_summary_subject') }}

{{ __('email.weekly_summary_intro') }}

@foreach($weekdays as $dateString => $dayData)
<div style="margin: 28px 0 24px;">
<h2 style="font-size:20px;line-height:26px;color:#18181B;margin:0 0 14px;padding:0;text-align:left;">
{{ __('email.weekday_' . strtolower($dayData['date']->format('l'))) }} - {{ $dayData['date']->format('M j, Y') }}
</h2>

@foreach($dayData['videos'] as $video)
<div style="margin-bottom:16px;padding:20px;border:0;border-radius:16px;background-color:#FFF0F2;">
<div style="margin-bottom:12px;">
<h3 style="margin:0 0 6px;font-size:18px;line-height:24px;color:#18181B;">
<a href="{{ $video->getYoutubeUrl() }}" style="text-decoration:none;color:#18181B;">
{{ $video->title }}
</a>
</h3>
<p style="margin:0;color:#52525B;font-size:14px;line-height:20px;">
@if(isset($video->channel))
{!! __('email.by_creator', ['creator' => '<a href="' . $video->channel->getChannelUrl() . '" style="color:#71717A;text-decoration:underline;">' . $video->channel->name . '</a>']) !!}
• {{ $video->getFormattedPublishedDate() }}
@endif
</p>
</div>

<div style="text-align:center;margin:12px 0 16px;">
<a href="{{ $video->getYoutubeUrl() }}" style="display: inline-block;">
<img src="{{ $video->getThumbnailUrl('maxresdefault') }}" alt="{{ $video->title }}" width="400" style="display:block;width:100%;max-width:400px;max-height:225px;height:auto;margin:0 auto;border:0;border-radius:16px;">
</a>
</div>

<div style="text-align: center;">
<x-mail::button :url="$video->getYoutubeUrl()" color="primary" style="display: inline-block; margin: 0 auto;">
<span style="padding-right:7px;font-size:14px;">&#9654;</span>
{{ __('email.watch_on_youtube') }}
</x-mail::button>
</div>
</div>
@endforeach
</div>
@endforeach
<x-slot:subcopy>
{!! __('email.weekly_summary_notification_reason') !!} {!! __('email.documentation_link', ['github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier?tab=readme-ov-file#youtube-channel-notifier-ycn-project']) !!}
</x-slot:subcopy>
</x-mail::message>

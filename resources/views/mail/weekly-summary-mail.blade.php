<?php

declare(strict_types=1);

?>
<x-mail::message>
<div style="margin:0 0 28px;padding:0 4px;">
<h1 style="margin:0;color:#18181B;font-size:26px;line-height:32px;font-weight:700;text-align:left;">{{ __('email.weekly_summary_subject') }}</h1>
<p style="margin:8px 0 0;color:#52525B;font-size:15px;line-height:22px;text-align:left;">{{ __('email.weekly_summary_intro') }}</p>
</div>

@foreach($weekdays as $dateString => $dayData)
<div style="margin: 28px 0 24px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0 0 14px;background-color:#FFF0F2;border-radius:14px;border-collapse:separate;border-spacing:0;">
<tr>
<td style="padding:13px 18px;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;font-size:16px;line-height:22px;font-weight:700;">
<span style="color:#FF0033;">{{ __('email.weekday_' . strtolower($dayData['date']->format('l'))) }}</span>
<span style="color:#71717A;font-size:14px;font-weight:500;">&nbsp;·&nbsp;{{ $dayData['date']->format('M j, Y') }}</span>
</td>
</tr>
</table>

@foreach($dayData['videos'] as $video)
<div style="margin-bottom:16px;padding:20px;border:0;border-radius:16px;background-color:#F4F4F5;">
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
<img src="{{ $video->getThumbnailUrl('maxresdefault') }}" alt="{{ $video->title }}" width="520" style="display:block;width:100%;max-width:520px;height:auto;margin:0 auto;border:0;border-radius:16px;">
</a>
</div>

<div style="text-align: center;">
<x-mail::button :url="$video->getYoutubeUrl()" color="primary" style="display: inline-block; margin: 0 auto;">
<span style="padding-right:7px;font-size:14px;">&#9654;</span>
{{ __('email.watch_on_youtube') }}
</x-mail::button>
</div>
<p style="margin:16px 0 0;color:#71717A;font-size:14px;line-height:20px;text-align:center;">
{!! str_replace('<a ', '<a style="color:#71717A;text-decoration:underline;" ', __('email.alternative_watch_link', ['link' => $video->getYoutubeUrl()])) !!}
</p>
</div>
@endforeach
</div>
@endforeach
<x-slot:subcopy>
{!! __('email.weekly_summary_notification_reason') !!} {!! __('email.documentation_link', ['github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier?tab=readme-ov-file#youtube-channel-notifier-ycn-project']) !!}
</x-slot:subcopy>
</x-mail::message>

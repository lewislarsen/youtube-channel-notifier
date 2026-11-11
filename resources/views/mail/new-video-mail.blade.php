<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ __('email.subject_new_upload', ['channel' => $videoCreator, 'title' => $videoTitle]) }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .email-gutter { padding-left: 16px !important; padding-right: 16px !important; }
            .email-header { padding-left: 24px !important; padding-right: 24px !important; }
            .email-content { padding-left: 0 !important; padding-right: 0 !important; }
            .video-title { font-size: 20px !important; }
        }
    </style>
</head>
<body class="email-page" style="margin:0; padding:0; width:100%; background-color:#F4F4F5; color:#18181B; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; -webkit-text-size-adjust:100%;">
    <!-- Header -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#F4F4F5;">
        <tr>
            <td align="center" style="padding:0;">
                <table role="presentation" class="email-container" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%; min-height:100vh; max-width:600px; margin:0 auto; background-color:#FFFFFF;">
                    <tr>
                        <td class="email-header" height="72" style="height:72px; padding:0 32px; background-color:#FFF0F2; color:#FF0033; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:20px; line-height:24px; font-weight:700;">
                            YouTube Channel Notifier
                        </td>
                    </tr>
                    <!-- Main content -->
                    <tr>
                        <td class="email-main" style="padding:0; color:#18181B;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="email-gutter" style="padding:0 16px;">
                                        <!-- Channel card -->
                                        <table role="presentation" class="email-card" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#FFF0F2; border-radius:16px;">
                                            <tr>
                                                <td style="padding:20px; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif;">
                                                    <div style="font-size:18px; line-height:24px; color:#18181B;"><strong>{{ $videoCreator }}</strong> {{ __('email.creator_uploaded_video') }}</div>
                                                    <div class="email-muted" style="padding-top:4px; color:#52525B; font-size:14px; line-height:20px;">{{ __('email.published_date', ['date' => $published]) }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Thumbnail -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:20px;">
                                            <tr>
                                                <td>
                                                    <a href="{{ $videoUrl }}" style="display:block; color:#FF0033; text-decoration:none;">
                                                        <img src="{{ $thumbnailUrl }}" alt="{{ $videoTitle }}" width="400" style="display:block; width:100%; max-width:400px; height:auto; margin:0 auto; border:0; border-radius:16px;">
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Video title -->
                                        <h1 class="video-title" style="margin:20px 0 0; color:#18181B; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:20px; line-height:1.3; font-weight:700; text-align:center;">{{ $videoTitle }}</h1>
                                        <!-- CTA button -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;">
                                            <tr>
                                                <td align="center">
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $videoUrl }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="50%" stroke="f" fillcolor="#FF0033">
                                                        <w:anchorlock/>
                                                        <center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">&#9654;&nbsp; {{ __('email.watch_on_youtube') }}</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-- -->
                                                    <a href="{{ $videoUrl }}" style="display:inline-block; max-width:100%; box-sizing:border-box; padding:14px 28px; border-radius:999px; background-color:#FF0033; color:#FFFFFF; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:16px; line-height:20px; font-weight:600; text-decoration:none; text-align:center; mso-padding-alt:0;">
                                                        <span style="padding-right:7px; font-size:14px;">&#9654;</span>{{ __('email.watch_on_youtube') }}
                                                    </a>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Fallback link -->
                                        <p class="email-muted" style="margin:20px 0 0; color:#52525B; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:14px; line-height:20px; text-align:center;">
                                            {!! str_replace('<a ', '<a style="color:#71717A;text-decoration:underline;" ', __('email.alternative_watch_link', ['link' => $videoUrl])) !!}
                                        </p>
                                    </td>
                                </tr>
                                <!-- Footer card -->
                                <tr>
                                    <td class="email-gutter" style="padding:32px 16px 0;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#0F0F0F; border-radius:16px;">
                                            <tr>
                                                <td style="padding:32px 24px; color:#FFFFFF; font-family:Inter, -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:13px; line-height:20px; text-align:center;">
                                                    <div>
                                                        {!! __('email.notification_reason', ['creator' => $videoCreator]) !!}
                                                        {!! str_replace('<a ', '<a style="color:#FFFFFF;text-decoration:underline;" ', __('email.documentation_link', ['github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier?tab=readme-ov-file#youtube-channel-notifier-ycn-project'])) !!}
                                                    </div>
                                                    <div style="padding-top:16px; color:#A1A1AA; font-size:12px; line-height:18px;">
                                                        {!! str_replace('<a ', '<a style="color:#A1A1AA;text-decoration:underline;" ', __('email.automated_notification', ['app_name' => Config::get('app.name'), 'github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier'])) !!}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Bottom spacing -->
                                <tr><td height="24" style="height:24px; line-height:24px; font-size:1px;">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

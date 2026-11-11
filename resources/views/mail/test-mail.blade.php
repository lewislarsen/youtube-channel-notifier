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
    <title>Notification email test</title>
</head>
<body style="margin:0;padding:0;width:100%;background-color:#F4F4F5;color:#18181B;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#F4F4F5;border-collapse:collapse;border-spacing:0;">
        <tr>
            <td align="center" style="padding:0;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;min-height:100vh;margin:0 auto;background-color:#FFFFFF;border-collapse:collapse;border-spacing:0;">
                    <!-- Header -->
                    <tr>
                        <td height="72" style="height:72px;padding:0 32px;background-color:#FFF0F2;color:#FF0033;font-size:20px;line-height:24px;font-weight:700;">
                            YouTube Channel Notifier
                        </td>
                    </tr>
                    <!-- Test message -->
                    <tr>
                        <td valign="top" style="padding:24px 16px;color:#18181B;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#FFF0F2;border-radius:16px;border-collapse:separate;border-spacing:0;">
                                <tr>
                                    <td style="padding:24px;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;">
                                        <h1 style="margin:0;color:#18181B;font-size:20px;line-height:1.3;font-weight:700;">Notification email test</h1>
                                        <p style="margin:12px 0 0;color:#52525B;font-size:15px;line-height:1.6;">If you are receiving this email, your connection to your SMTP provider is working correctly.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:0 16px 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#0F0F0F;border-radius:16px;border-collapse:separate;border-spacing:0;">
                                <tr>
                                    <td style="padding:32px 24px;color:#A1A1AA;font-size:12px;line-height:18px;text-align:center;">
                                        {!! str_replace('<a ', '<a style="color:#A1A1AA;text-decoration:underline;" ', __('email.automated_notification', ['app_name' => Config::get('app.name'), 'github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier'])) !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

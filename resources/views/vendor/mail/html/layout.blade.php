<?php

declare(strict_types=1);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="background-color:#F4F4F5">
<head>
    <title>{{ Config::get('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    {{ $head ?? '' }}
</head>
<body style="margin:0;padding:0;background-color:#F4F4F5;color:#18181B;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;">
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:600px;min-height:100vh;margin:0 auto;background-color:#FFFFFF;">
                {{ $header ?? '' }}

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border:0;background-color:#FFFFFF;">
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:600px;background-color:#FFFFFF;border:0;margin:0 auto;">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell" style="padding:24px 16px 24px;">
                                    {{ Illuminate\Mail\Markdown::parse($slot) }}

                                    {{ $subcopy ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{ $footer ?? '' }}
            </table>
        </td>
    </tr>
</table>
</body>
</html>
<?php

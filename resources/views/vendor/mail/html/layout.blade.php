<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ Config::get('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        /* Base */
        body, body *:not(html):not(style):not(br):not(tr):not(code) {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
            'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            position: relative;
        }

        body {
            -webkit-text-size-adjust: none;
            background-color: #f9fafb;
            color: #4b5563;
            height: 100%;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        p, ul, ol, blockquote {
            line-height: 1.5;
            text-align: left;
        }

        a {
            color: #0ea5e9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        a img {
            border: none;
        }

        /* Typography */
        h1 {
            color: #111827;
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
            text-align: center;
        }

        h2 {
            color: #111827;
            font-size: 20px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        h3 {
            color: #111827;
            font-size: 16px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }

        p.sub {
            font-size: 12px;
        }

        img {
            max-width: 100%;
        }

        /* Layout */
        .wrapper {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .content {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* Header */
        .header {
            padding: 25px 0;
            text-align: center;
            background-color: #0ea5e9;
        }

        .header a {
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        /* Logo */
        .logo {
            height: 75px;
            max-height: 75px;
            width: 75px;
        }

        /* Body */
        .body {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
            background-color: #ffffff;
            border-color: #e5e7eb;
            border-radius: 8px;
            border-width: 1px;
            box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015);
            margin: 0 auto;
            padding: 0;
            width: 570px;
            overflow: hidden;
        }

        /* Subcopy */
        .subcopy {
            border-top: 1px solid #e5e7eb;
            margin-top: 25px;
            padding-top: 25px;
        }

        .subcopy p {
            font-size: 14px;
            color: #6b7280;
        }

        /* Footer */
        .footer {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
            margin: 0 auto;
            padding: 0;
            text-align: center;
            width: 570px;
        }

        .footer p {
            color: #6b7280;
            font-size: 12px;
            text-align: center;
        }

        .footer a {
            color: #6b7280;
            text-decoration: underline;
        }

        /* Tables */
        .table table {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 30px auto;
            width: 100%;
        }

        .table th {
            border-bottom: 1px solid #e5e7eb;
            margin: 0;
            padding-bottom: 8px;
            color: #111827;
        }

        .table td {
            color: #4b5563;
            font-size: 15px;
            line-height: 18px;
            margin: 0;
            padding: 10px 0;
        }

        .content-cell {
            max-width: 100vw;
            padding: 32px;
        }

        /* Buttons */
        .action {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 30px auto;
            padding: 0;
            text-align: center;
            width: 100%;
        }

        .button {
            -webkit-text-size-adjust: none;
            border-radius: 8px;
            color: #fff;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
        }

        .button-blue,
        .button-primary {
            background-color: #0ea5e9;
            border-bottom: 8px solid #0ea5e9;
            border-left: 18px solid #0ea5e9;
            border-right: 18px solid #0ea5e9;
            border-top: 8px solid #0ea5e9;
        }

        .button-green,
        .button-success {
            background-color: #10b981;
            border-bottom: 8px solid #10b981;
            border-left: 18px solid #10b981;
            border-right: 18px solid #10b981;
            border-top: 8px solid #10b981;
        }

        .button-red,
        .button-error {
            background-color: #ef4444;
            border-bottom: 8px solid #ef4444;
            border-left: 18px solid #ef4444;
            border-right: 18px solid #ef4444;
            border-top: 8px solid #ef4444;
        }

        /* Panels */
        .panel {
            border-left: #0ea5e9 solid 4px;
            margin: 21px 0;
        }

        .panel-content {
            background-color: #f9fafb;
            color: #4b5563;
            padding: 16px;
        }

        .panel-content p {
            color: #4b5563;
        }

        .panel-item {
            padding: 0;
        }

        .panel-item p:last-of-type {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Header gradient - simplified for email clients */
        .header-gradient {
            background-color: #0ea5e9;
            padding: 24px 0;
            text-align: center;
        }

        /* Utilities */
        .break-all {
            word-break: break-all;
        }

        /* Media Queries */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
    {{ $head ?? '' }}
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                {{ $header ?? '' }}

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
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

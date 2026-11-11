<?php

declare(strict_types=1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YouTube Channel Notifier</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('gray-favicon.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('white-favicon.ico') }}" media="(prefers-color-scheme: dark)">

    <meta property="og:title" content="YouTube Channel Notifier">
    <meta property="og:description" content="Get notified when your favourite YouTube channels upload new content.">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:url" content="{{ URL::current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="YouTube Channel Notifier">
    <meta name="twitter:description" content="Get notified when your favourite YouTube channels upload new content.">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">
    <meta name="theme-color" content="#0F0F0F">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            color-scheme: light;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #18181B;
            background: #F4F4F5;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 48px 20px;
            background: #F4F4F5;
        }

        a { color: inherit; }

        .page-shell { width: min(100%, 920px); margin: 0 auto; }

        .hero {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 180px;
            align-items: center;
            gap: 32px;
            overflow: hidden;
            padding: clamp(28px, 6vw, 56px);
            border-radius: 24px;
            background: #0F0F0F;
            color: #FFFFFF;
        }

        .hero::before {
            position: absolute;
            top: -120px;
            right: 10%;
            width: 300px;
            height: 300px;
            border: 1px solid rgba(255, 0, 51, .22);
            border-radius: 50%;
            content: '';
        }

        .hero-copy { position: relative; z-index: 1; }

        h1 {
            max-width: 620px;
            margin: 0;
            font-size: clamp(36px, 6vw, 58px);
            line-height: 1.04;
            letter-spacing: -.045em;
        }

        .hero-lede {
            max-width: 540px;
            margin: 18px 0 0;
            color: #D4D4D8;
            font-size: 17px;
            line-height: 1.65;
        }

        .hero-art {
            position: relative;
            display: flex;
            height: 150px;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .hero-art::before,
        .hero-art::after {
            position: absolute;
            width: 126px;
            height: 126px;
            border: 1px solid rgba(255, 0, 51, .25);
            border-radius: 50%;
            content: '';
        }

        .hero-art::after { width: 96px; height: 96px; border-color: rgba(255, 0, 51, .4); }

        .signal-bar {
            z-index: 1;
            width: 7px;
            border-radius: 999px;
            background: #FF0033;
        }

        .signal-bar:nth-child(1), .signal-bar:nth-child(7) { height: 22px; opacity: .55; }
        .signal-bar:nth-child(2), .signal-bar:nth-child(6) { height: 42px; opacity: .72; }
        .signal-bar:nth-child(3), .signal-bar:nth-child(5) { height: 62px; }
        .signal-bar:nth-child(4) { height: 82px; }

        .github-link {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
            padding: 0 22px;
            border-radius: 999px;
            background: #FF0033;
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 650;
            text-decoration: none;
            transition: background-color .15s ease, transform .15s ease;
        }

        .github-link:hover { background: #E6002E; transform: translateY(-1px); }
        .github-link:focus-visible, summary:focus-visible { outline: 3px solid #FF8AA1; outline-offset: 3px; }
        .github-link svg { width: 18px; height: 18px; }

        .intro-card {
            margin-top: 16px;
            padding: 24px 28px;
            border-radius: 16px;
            background: #FFF0F2;
            color: #52525B;
            font-size: 16px;
            line-height: 1.65;
        }

        .faq { margin-top: 44px; }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .section-heading h2 { margin: 0; font-size: 25px; letter-spacing: -.025em; }
        .section-heading p { margin: 0; color: #71717A; font-size: 13px; }

        .faq-list { display: grid; gap: 10px; }

        .faq-item {
            overflow: hidden;
            border-radius: 16px;
            background: #FFFFFF;
        }

        .faq-item summary {
            display: flex;
            min-height: 64px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            color: #18181B;
            cursor: pointer;
            font-size: 16px;
            font-weight: 650;
            list-style: none;
        }

        .faq-item summary::-webkit-details-marker { display: none; }

        .faq-item summary::after {
            flex: 0 0 auto;
            color: #FF0033;
            content: '+';
            font-size: 24px;
            font-weight: 400;
            line-height: 1;
        }

        .faq-item[open] summary::after { content: '−'; }

        .faq-answer {
            max-width: 760px;
            padding: 0 22px 22px;
            color: #52525B;
            font-size: 15px;
            line-height: 1.7;
        }

        .site-footer {
            margin-top: 44px;
            padding: 24px;
            border-radius: 16px;
            background: #0F0F0F;
            color: #A1A1AA;
            font-size: 13px;
            line-height: 1.6;
            text-align: center;
        }

        .site-footer p { margin: 0; }
        .site-footer a { color: #FFFFFF; text-decoration: underline; text-underline-offset: 3px; }

        @media (max-width: 640px) {
            body { padding: 20px 14px; }
            .hero { grid-template-columns: 1fr; gap: 8px; padding: 30px 24px; border-radius: 20px; }
            .hero-art { position: absolute; right: -50px; bottom: -35px; width: 160px; height: 140px; opacity: .55; }
            .hero-copy { max-width: 100%; }
            .hero-lede { font-size: 16px; }
            .intro-card { padding: 20px; }
            .faq { margin-top: 34px; }
            .section-heading { display: block; }
            .section-heading p { margin-top: 6px; }
            .site-footer { margin-top: 32px; }
        }
    </style>
</head>
<body>
    <main class="page-shell">
        <section class="hero" aria-labelledby="page-title">
            <div class="hero-copy">
                <h1 id="page-title">YouTube Channel Notifier</h1>
                <p class="hero-lede">Get a notification when a channel you follow uploads a new video—by email, Discord, or POST webhook.</p>
                <a class="github-link" href="https://github.com/lewislarsen/youtube-channel-notifier" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.66-.22.66-.48v-1.69c-2.78.6-3.37-1.2-3.37-1.2-.46-1.17-1.12-1.48-1.12-1.48-.91-.62.07-.61.07-.61 1.01.07 1.54 1.04 1.54 1.04.9 1.54 2.36 1.1 2.94.84.09-.65.35-1.1.63-1.35-2.22-.25-4.56-1.11-4.56-4.93 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.57 9.57 0 0112 6.8c.85.004 1.71.11 2.51.32 1.91-1.3 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.83-2.34 4.67-4.57 4.92.36.31.67.91.67 1.84v2.73c0 .27.16.58.67.48A10.012 10.012 0 0022 12c0-5.52-4.48-10-10-10z" />
                    </svg>
                    View on GitHub
                </a>
            </div>
            <div class="hero-art" aria-hidden="true">
                <span class="signal-bar"></span><span class="signal-bar"></span><span class="signal-bar"></span>
                <span class="signal-bar"></span><span class="signal-bar"></span><span class="signal-bar"></span>
                <span class="signal-bar"></span>
            </div>
        </section>

        <p class="intro-card">Choose email, Discord, or a JSON POST to a URL you configure, and keep up with your favourite channels without checking YouTube for new uploads.</p>

        <section class="faq" aria-labelledby="faq-title">
            <div class="section-heading">
                <h2 id="faq-title">Frequently Asked Questions</h2>
            </div>

            <div class="faq-list">
                <details class="faq-item">
                    <summary>What is this?</summary>
                    <div class="faq-answer">YouTube Channel Notifier checks the channels you choose and sends new-upload notifications by email, Discord, or a JSON POST request to your configured webhook URL. No need to keep checking YouTube for updates.</div>
                </details>
                <details class="faq-item">
                    <summary>How can I use it?</summary>
                    <div class="faq-answer">The setup guide is in the GitHub repository. You’ll need to run your own instance; the guide walks you through the steps.</div>
                </details>
                <details class="faq-item">
                    <summary>What are the costs involved?</summary>
                    <div class="faq-answer">The software is free and open source. You’ll need to arrange hosting for your instance; the cost depends on the service and setup you choose.</div>
                </details>
            </div>
        </section>

        <footer class="site-footer">
            <p>Open source project by <a href="https://github.com/lewislarsen" target="_blank" rel="noopener noreferrer">Lewis Larsen</a></p>
        </footer>
    </main>
</body>
</html>

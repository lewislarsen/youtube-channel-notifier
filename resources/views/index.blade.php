<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YouTube Channel Notifier</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <!-- Social Media Meta Tags -->
    <meta property="og:title" content="YouTube Channel Notifier">
    <meta property="og:description" content="Get notified when your favorite YouTube channels upload new content.">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:url" content="{{ URL::current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="YouTube Channel Notifier">
    <meta name="twitter:description" content="Get notified when your favorite YouTube channels upload new content.">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">

    <!-- Theme Color -->
    <meta name="theme-color" content="#FF0000">

    <!-- Styles -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        /* Force the background color */
        html {
            background-color: #f9fafb; /* gray-50 */
        }

        html.dark {
            background-color: #111827; /* gray-900 */
        }

        body {
            background-color: #f9fafb; /* gray-50 */
        }

        .dark body {
            background-color: #111827; /* gray-900 */
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .bg-gradient-pattern {
            background-image:
                radial-gradient(circle at 25% 10%, rgba(255, 0, 0, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
            background-attachment: fixed;
        }

        .glass-card {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .perspective-1000 {
            perspective: 1000px;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 bg-gradient-pattern flex items-center justify-center p-4 text-gray-900 dark:text-gray-100"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || window.matchMedia('(prefers-color-scheme: dark)').matches }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) });
              document.documentElement.classList.toggle('dark', darkMode)"
      :class="{ 'dark': darkMode }">

<!-- Main Content Card -->
<div class="max-w-lg w-full perspective-1000">
    <div class="relative">
        <!-- Decorative Elements -->
        <div class="absolute -top-6 -right-8 w-24 h-24 bg-red-600 rounded-full opacity-30 blur-2xl"></div>
        <div class="absolute -bottom-6 -left-8 w-20 h-20 bg-red-600 rounded-full opacity-20 blur-xl"></div>

        <!-- Card Content -->
        <div class="relative bg-white/90 dark:bg-gray-800/90 glass-card rounded-2xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden z-10">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" class="absolute h-full w-full">
                        <circle cx="512" cy="512" r="512" fill="white" />
                        <circle cx="512" cy="512" r="400" fill="white" opacity="0.7" />
                        <circle cx="512" cy="512" r="300" fill="white" opacity="0.5" />
                    </svg>
                </div>

                <div class="relative">
                    <div class="float-animation inline-block bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-lg mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>

                    <h1 class="text-3xl font-bold text-white">YouTube Channel Notifier</h1>
                    <p class="text-red-100 mt-2 max-w-md mx-auto">Never miss a video upload from your favorite creators again.</p>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <div class="flex justify-center mb-6">
                    <p class="text-gray-600 dark:text-gray-300 text-center max-w-md">
                        Stay informed when channels you follow post new content. Simple, reliable, and customizable notifications.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="flex justify-center mb-8">
                    <a href="https://github.com/lewislarsen/youtube-channel-notifier" target="_blank"
                       class="inline-flex items-center px-6 py-3 text-white font-semibold bg-gradient-to-r from-red-600 to-red-700 rounded-xl shadow-lg hover:shadow-xl hover:from-red-700 hover:to-red-800 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.66-.22.66-.48v-1.69c-2.78.6-3.37-1.2-3.37-1.2-.46-1.17-1.12-1.48-1.12-1.48-.91-.62.07-.61.07-.61 1.01.07 1.54 1.04 1.54 1.04.9 1.54 2.36 1.1 2.94.84.09-.65.35-1.1.63-1.35-2.22-.25-4.56-1.11-4.56-4.93 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.57 9.57 0 0112 6.8c.85.004 1.71.11 2.51.32 1.91-1.3 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.83-2.34 4.67-4.57 4.92.36.31.67.91.67 1.84v2.73c0 .27.16.58.67.48A10.012 10.012 0 0022 12c0-5.52-4.48-10-10-10z" />
                        </svg>
                        View on GitHub
                    </a>
                </div>

                <!-- Theme Toggle -->
                <div class="flex justify-center mb-6">
                    <button @click="darkMode = !darkMode" class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-600 transition-colors">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span x-text="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"></span>
                    </button>
                </div>

                <!-- FAQ Section -->
                <div class="mt-6 text-left" x-data="{ open: null }">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Frequently Asked Questions</h2>

                    <div class="mb-3 border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <button @click="open = open === 1 ? null : 1"
                                class="w-full text-left p-4 font-medium text-gray-800 dark:text-white flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                            What is this service?
                            <span class="text-red-600 text-xl" x-text="open === 1 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 1" x-transition class="p-4 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/30">
                            This is a YouTube Channel Notifier, a tool that monitors your favorite YouTube channels and notifies you whenever they upload new content. It helps you stay up-to-date without constantly checking YouTube.
                        </div>
                    </div>

                    <div class="mb-3 border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <button @click="open = open === 2 ? null : 2"
                                class="w-full text-left p-4 font-medium text-gray-800 dark:text-white flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                            How can I use it?
                            <span class="text-red-600 text-xl" x-text="open === 2 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 2" x-transition class="p-4 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/30">
                            Follow the detailed instructions in the GitHub repository. You'll need to host it yourself, but the setup process is straightforward and well-documented.
                        </div>
                    </div>

                    <div class="mb-3 border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <button @click="open = open === 3 ? null : 3"
                                class="w-full text-left p-4 font-medium text-gray-800 dark:text-white flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                            What are the costs involved?
                            <span class="text-red-600 text-xl" x-text="open === 3 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 3" x-transition class="p-4 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/30">
                            This project is completely free and open source. You only need to cover the costs of hosting it yourself, which can be very minimal or even free depending on your setup.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700/50 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>Open source project by <a href="https://github.com/lewislarsen" target="_blank" class="text-red-600 hover:underline">Lewis Larsen</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

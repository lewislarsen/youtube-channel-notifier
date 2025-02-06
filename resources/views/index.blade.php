<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Channel Notifier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center h-screen">
<div class="text-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full">
    <div class="flex justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-28 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">YouTube Channel Notifier</h1>
    <p class="text-gray-600 dark:text-gray-300 mt-2">You've reached a Youtube Channel Notifier instance.</p>
    <a href="https://github.com/lewislarsen/youtube-channel-notifier" target="_blank" class="inline-flex items-center mt-4 px-4 py-2 text-white font-semibold bg-gradient-to-r from-sky-500 to-sky-600 rounded-xl shadow-lg hover:from-sky-600 hover:to-sky-700 transition-all">
        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.66-.22.66-.48v-1.69c-2.78.6-3.37-1.2-3.37-1.2-.46-1.17-1.12-1.48-1.12-1.48-.91-.62.07-.61.07-.61 1.01.07 1.54 1.04 1.54 1.04.9 1.54 2.36 1.1 2.94.84.09-.65.35-1.1.63-1.35-2.22-.25-4.56-1.11-4.56-4.93 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.57 9.57 0 0112 6.8c.85.004 1.71.11 2.51.32 1.91-1.3 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.83-2.34 4.67-4.57 4.92.36.31.67.91.67 1.84v2.73c0 .27.16.58.67.48A10.012 10.012 0 0022 12c0-5.52-4.48-10-10-10z" />
        </svg>
        Visit GitHub
    </a>

    <!-- FAQ Section -->
    <div class="mt-8 text-left" x-data="{ open: null }">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Questions</h2>
        <div class="border-b border-gray-300 dark:border-gray-600" x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2 font-semibold text-gray-800 dark:text-white flex justify-between">
                What is this page about?
                <span x-text="open ? '-' : '+'"></span>
            </button>
            <p x-show="open" class="text-gray-600 dark:text-gray-300 py-2">This page is a home section for an instance of a YouTube Channel Notifier, a project that informs individuals when a specific channel has uploaded content.</p>
        </div>
        <div class="border-b border-gray-300 dark:border-gray-600" x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2 font-semibold text-gray-800 dark:text-white flex justify-between">
                How can I use it?
                <span x-text="open ? '-' : '+'"></span>
            </button>
            <p x-show="open" class="text-gray-600 dark:text-gray-300 py-2">Follow the instructions located in the GitHub repository.</p>
        </div>
        <div class="border-b border-gray-300 dark:border-gray-600" x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2 font-semibold text-gray-800 dark:text-white flex justify-between">
                What fees are involved?
                <span x-text="open ? '-' : '+'"></span>
            </button>
            <p x-show="open" class="text-gray-600 dark:text-gray-300 py-2">This project is free and is open source. You will need to host it yourself.</p>
        </div>
    </div>
</div>
</body>
</html>

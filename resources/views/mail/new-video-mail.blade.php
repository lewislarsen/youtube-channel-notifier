<x-mail::message>
# New Video Alert: {{ $videoTitle }}

Hello,

A new video has just been uploaded by **{{ $videoCreator }}** on their YouTube channel!


**Published On:** {{ $published }}

Ready to dive in? Click the button below to watch now:

<x-mail::button :url="$videoUrl" color="primary">
Watch Now on YouTube
</x-mail::button>

Hope you enjoy the content.
</x-mail::message>

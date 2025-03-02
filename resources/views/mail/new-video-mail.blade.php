<x-mail::message>
# New Video Upload

**{{ $videoCreator }}** has uploaded a new video:

## {{ $videoTitle }}

<div style="text-align: center; margin: 25px 0;">
 <a href="{{ $videoUrl }}" style="display: inline-block;">
 <img src="{{ $thumbnailUrl }}" alt="{{ $videoTitle }}" style="max-width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
</a>
</div>

<p style="text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 25px;">
Published: {{ $published }}
</p>

<x-mail::button :url="$videoUrl" color="primary">
Watch Now on YouTube
</x-mail::button>

 <x-mail::subcopy>
You're receiving this notification because you are subscribed to video releases from <a href="{{ $videoCreatorUrl }}">{{ $videoCreator }}</a>.
</x-mail::subcopy>
</x-mail::message>

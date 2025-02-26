<x-mail::message>
# New Video Alert: {{ $videoTitle }}

**{{ $videoCreator }}** has just uploaded a new video:

<div style="text-align: center; margin: 20px 0;">
<a href="{{ $videoUrl }}" style="display: inline-block;">
<img src="{{ $thumbnailUrl }}" alt="Video thumbnail" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
</a>
</div>

**Published:** {{ $published }}

<x-mail::button :url="$videoUrl" color="primary">
Watch Now
</x-mail::button>

Stay up to date with all the latest uploads from **{{ $videoCreator }}**!
</x-mail::message>

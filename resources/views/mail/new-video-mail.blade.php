<x-mail::message>
# New Video Alert: {{ $videoTitle }}

**Published At:** {{ $publishedAt }}

<x-mail::button :url="$videoUrl">
Watch Now
</x-mail::button>

</x-mail::message>

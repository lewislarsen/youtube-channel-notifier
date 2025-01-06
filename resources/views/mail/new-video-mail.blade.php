<x-mail::message>
# New Video from {{ $videoCreator }}

Hello,

**{{ $videoCreator }}** has just released a new video on their YouTube channel:
**{{ $videoTitle }}**

### Details:
**Published On:** {{ $published }}

Looking for something exciting? Don’t miss out, watch it now!

<x-mail::button :url="$videoUrl" color="primary">
Watch "{{ $videoTitle }}" on YouTube
</x-mail::button>

Thank you for staying connected with **{{ $videoCreator }}**. We hope you enjoy their latest content!
</x-mail::message>

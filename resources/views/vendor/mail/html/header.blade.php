@props(['url'])
<tr>
    <td class="header">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <a href="{{ $url }}" style="display: inline-block;">
                        <h1 style="color: #ffffff; font-size: 28px; font-weight: bold; margin: 0; text-align: center;">{{ $slot }}</h1>
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>

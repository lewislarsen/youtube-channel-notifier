<?php

declare(strict_types=1);

?>
@props(['url'])
<tr>
                <td class="header" style="height:72px;padding:0 32px;background-color:#FFF0F2;text-align:left;">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="left" style="height:72px;vertical-align:middle;">
                    <a href="{{ $url }}" style="display:inline-block;color:#FF0033;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;font-size:20px;line-height:24px;font-weight:700;text-decoration:none;">
                        {{ $slot }}
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php

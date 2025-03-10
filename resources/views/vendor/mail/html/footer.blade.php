<?php

declare(strict_types=1);

?>
<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center" style="padding: 16px 32px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php 

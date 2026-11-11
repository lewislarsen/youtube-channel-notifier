<?php

declare(strict_types=1);

?>
<tr>
    <td>
        <table class="footer" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center" style="padding:0 16px 24px;">
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;background-color:#0F0F0F;border-radius:16px;">
                        <tr>
                            <td style="padding:32px 24px;color:#FFFFFF;font-family:Inter,-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:20px;text-align:center;">
                                {!! $slot !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php

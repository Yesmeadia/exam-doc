@props(['url'])
<tr>
<td class="header" align="center" style="padding: 28px 0 20px; text-align: center;">
<table cellpadding="0" cellspacing="0" border="0" align="center" role="presentation" style="margin: 0 auto; text-align: left;">
<tr>
<td style="vertical-align: middle; padding-right: 14px;">
    <a href="{{ $url }}" style="text-decoration: none; display: block;">
        <img src="{{ url('icons/logo.svg') }}" onerror="this.onerror=null; this.src='{{ url('icons/logo.png') }}';" alt="RUIHSS POONCH Logo" width="52" height="52" style="display: block; width: 52px; height: 52px; max-width: 52px; max-height: 52px; border: 0;" />
    </a>
</td>
<td style="vertical-align: middle; text-align: left;">
    <a href="{{ $url }}" style="text-decoration: none; color: #0f172a; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; display: block; line-height: 1.2;">
        {{ setting('school_short_name', 'RUIHSS POONCH') }}
    </a>
    <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.2;">
        {{ setting('app_name', 'RESULT MANAGEMENT SYSTEM') }}
    </div>
</td>
</tr>
</table>
</td>
</tr>

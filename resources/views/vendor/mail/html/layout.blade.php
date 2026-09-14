<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ setting('school_short_name', 'RUIHSS POONCH') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
    .inner-body {
        width: 100% !important;
    }

    .footer {
        width: 100% !important;
    }

    .content-cell {
        padding: 24px 16px !important;
    }

    .action {
        margin: 16px auto !important;
    }

    .button {
        width: auto !important;
        max-width: 95% !important;
        display: inline-block !important;
        padding: 8px 16px !important;
        font-size: 12.5px !important;
        border-radius: 6px !important;
        white-space: nowrap !important;
        word-break: normal !important;
    }
}

@media only screen and (max-width: 500px) {
    .content-cell {
        padding: 20px 12px !important;
    }

    .button {
        width: auto !important;
        display: inline-block !important;
        padding: 8px 14px !important;
        font-size: 12px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
        word-break: normal !important;
    }

    .action {
        margin: 14px auto !important;
    }
}
</style>
{!! $head ?? '' !!}
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; width: 100%; background-color: #f1f5f9;">
<tr>
<td align="center" style="padding: 20px 10px;">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; width: 100%; max-width: 600px;">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; width: 570px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); overflow: hidden;">
<!-- Body content -->
<tr>
<td class="content-cell" style="padding: 36px 32px; font-size: 15px; line-height: 1.6; color: #334155;">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>

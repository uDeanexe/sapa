<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
</head>
<body style="margin: 0; background: #f8fafc; color: #0f172a; font-family: Arial, sans-serif; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f8fafc; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff;">
                    <tr>
                        <td style="padding: 22px 24px 10px;">
                            <p style="margin: 0 0 6px; color: #059669; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">{{ config('app.name') }}</p>
                            <h1 style="margin: 0; color: #0f172a; font-size: 22px; line-height: 1.3;">{{ $title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 24px 20px;">
                            <p style="margin: 12px 0 0; color: #475569; font-size: 15px;">{{ $message }}</p>

                            @if($actionUrl)
                                <p style="margin: 22px 0 0;">
                                    <a href="{{ $actionUrl }}" style="display: inline-block; border-radius: 10px; background: #059669; padding: 10px 16px; color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none;">
                                        {{ $actionLabel }}
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid #e2e8f0; padding: 14px 24px; color: #64748b; font-size: 12px;">
                            Email ini dikirim otomatis. Abaikan jika notifikasi ini tidak relevan untuk Anda.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

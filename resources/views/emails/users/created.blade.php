<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Akun Sapa Jonusa</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <h2 style="margin-bottom: 12px;">Halo, {{ $user->name }}</h2>

    <p>Akun Sapa Jonusa Anda sudah dibuat. Silakan login menggunakan data berikut:</p>

    <table style="border-collapse: collapse; margin: 18px 0;">
        <tr>
            <td style="padding: 6px 12px 6px 0; font-weight: bold;">Email</td>
            <td style="padding: 6px 0;">{{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 12px 6px 0; font-weight: bold;">Password awal</td>
            <td style="padding: 6px 0;">{{ $defaultPassword }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 12px 6px 0; font-weight: bold;">Link login</td>
            <td style="padding: 6px 0;">
                <a href="{{ url('/login') }}">{{ url('/login') }}</a>
            </td>
        </tr>
    </table>

    <p>Untuk keamanan, segera ganti password setelah berhasil login.</p>

    <p style="margin-top: 24px;">
        Terima kasih,<br>
        {{ config('app.name') }}
    </p>
</body>
</html>

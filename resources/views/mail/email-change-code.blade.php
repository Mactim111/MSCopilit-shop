<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
</head>
<body>
    <p>Для привязки нового email к аккаунту в {{ config('app.name') }} введите код:</p>
    <p style="font-size: 24px; font-weight: bold; letter-spacing: 6px;">{{ $code }}</p>
    <p>Код действует 10 минут. Если вы не запрашивали его, просто проигнорируйте это письмо.</p>
</body>
</html>

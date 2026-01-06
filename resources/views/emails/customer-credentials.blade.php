<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a FATMAC</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #a855f7;
            margin: 0;
            font-size: 28px;
        }
        .credentials-box {
            background-color: #f0f9ff;
            border: 2px solid #a855f7;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-box h2 {
            color: #7c3aed;
            margin-top: 0;
            font-size: 20px;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 8px;
        }
        .credential-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 14px;
        }
        .credential-value {
            font-size: 18px;
            color: #1f2937;
            font-weight: bold;
            margin-top: 5px;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .button {
            display: inline-block;
            background-color: #a855f7;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Bienvenido a FATMAC!</h1>
        </div>

        <p>Hola <strong>{{ $user->name }}</strong>,</p>

        <p>Gracias por realizar tu compra en FATMAC. Se ha creado una cuenta para ti con las siguientes credenciales:</p>

        <div class="credentials-box">
            <h2>📧 Tus Credenciales de Acceso</h2>
            <div class="credential-item">
                <div class="credential-label">Email:</div>
                <div class="credential-value">{{ $user->email }}</div>
            </div>
            <div class="credential-item">
                <div class="credential-label">Contraseña:</div>
                <div class="credential-value">{{ $password }}</div>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña al ingresar por primera vez.
        </div>

        <p>Puedes iniciar sesión en nuestra plataforma para ver el estado de tus pedidos:</p>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Iniciar Sesión</a>
        </div>

        <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

        <div class="footer">
            <p>¡Gracias por confiar en FATMAC!</p>
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>

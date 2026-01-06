<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro como Vendedor - FATMAC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #a855f7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #fff;
            border-left: 4px solid #a855f7;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛍️ ¡Bienvenido a FATMAC!</h1>
    </div>
    <div class="content">
        <h2>Hola {{ $vendor->name }},</h2>
        
        <p>Gracias por registrarte como vendedor en FATMAC. Tu solicitud ha sido recibida correctamente y está en proceso de evaluación.</p>
        
        <div class="info-box">
            <h3>📋 Estado de tu Solicitud</h3>
            <p><strong>Estado actual:</strong> <span style="color: #f59e0b;">Pendiente de Aprobación</span></p>
            <p>Nuestro equipo de administración revisará tu solicitud y te notificará por correo electrónico cuando sea aprobada o rechazada.</p>
        </div>

        <div class="info-box">
            <h3>📧 Información de tu Cuenta</h3>
            <p><strong>Email:</strong> {{ $vendor->email }}</p>
            <p><strong>Nombre:</strong> {{ $vendor->name }}</p>
            @if($vendor->phone_number)
            <p><strong>Teléfono:</strong> {{ $vendor->phone_number }}</p>
            @endif
        </div>

        <p><strong>¿Qué sigue?</strong></p>
        <ul>
            <li>Espera la notificación de aprobación por correo electrónico</li>
            <li>Una vez aprobado, podrás iniciar sesión y comenzar a vender tus productos</li>
            <li>Podrás gestionar tus productos, ver tus ventas y configurar tus métodos de pago desde el dashboard</li>
        </ul>

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        
        <p>¡Esperamos tenerte pronto como parte de nuestra plataforma!</p>
        
        <p>Saludos,<br>
        <strong>El equipo de FATMAC</strong></p>
    </div>
</body>
</html>


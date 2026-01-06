<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@if($status === 'approved') Cuenta Aprobada @else Actualización de Cuenta @endif - FATMAC</title>
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
            background: @if($status === 'approved') linear-gradient(135deg, #10b981 0%, #059669 100%);
            @else linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            @endif
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
            background: @if($status === 'approved') #10b981;
            @else #ef4444;
            @endif
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #fff;
            border-left: 4px solid @if($status === 'approved') #10b981;
            @else #ef4444;
            @endif
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .warning {
            color: #ef4444;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>@if($status === 'approved') ✅ ¡Cuenta Aprobada! @else ❌ Actualización de Cuenta @endif</h1>
    </div>
    <div class="content">
        <h2>Hola {{ $vendor->name }},</h2>
        
        @if($status === 'approved')
            <p>¡Excelentes noticias! Tu solicitud para ser vendedor en FATMAC ha sido <span class="success">APROBADA</span>.</p>
            
            <div class="info-box">
                <h3>🎉 ¡Bienvenido a la plataforma!</h3>
                <p>Ya puedes comenzar a vender tus productos en FATMAC. Tu cuenta está activa y lista para usar.</p>
            </div>

            <p><strong>¿Qué puedes hacer ahora?</strong></p>
            <ul>
                <li>Iniciar sesión en tu cuenta</li>
                <li>Agregar tus productos al catálogo</li>
                <li>Configurar tus códigos QR para recibir pagos (Yape y Plin)</li>
                <li>Gestionar tus pedidos y ventas desde el dashboard</li>
                <li>Ver estadísticas de tus productos y ventas</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="button">Iniciar Sesión</a>
            </div>
        @else
            <p>Lamentamos informarte que tu solicitud para ser vendedor en FATMAC ha sido <span class="warning">RECHAZADA</span>.</p>
            
            <div class="info-box">
                <h3>📋 Información</h3>
                <p>Después de revisar tu solicitud, hemos decidido no aprobar tu cuenta de vendedor en este momento.</p>
            </div>

            <p>Si crees que esto es un error o deseas más información sobre esta decisión, por favor contacta con nuestro equipo de atención.</p>

            <p>Gracias por tu interés en formar parte de FATMAC.</p>
        @endif

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        
        <p>Saludos,<br>
        <strong>El equipo de FATMAC</strong></p>
    </div>
</body>
</html>


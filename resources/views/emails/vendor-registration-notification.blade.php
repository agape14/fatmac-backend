<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro de Vendedor - FATMAC</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #fff;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
        }
        .data-row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Nuevo Registro de Vendedor</h1>
    </div>
    <div class="content">
        <h2>Hola Administrador,</h2>
        
        <p>Se ha recibido una nueva solicitud de registro como vendedor que requiere tu revisión y aprobación.</p>
        
        <div class="info-box">
            <h3>📋 Información del Vendedor</h3>
            <div class="data-row">
                <strong>Nombre:</strong> {{ $vendor->name }}
            </div>
            <div class="data-row">
                <strong>Email:</strong> {{ $vendor->email }}
            </div>
            @if($vendor->phone_number)
            <div class="data-row">
                <strong>Teléfono:</strong> {{ $vendor->phone_number }}
            </div>
            @endif
            @if($vendor->whatsapp_number)
            <div class="data-row">
                <strong>WhatsApp:</strong> {{ $vendor->whatsapp_number }}
            </div>
            @endif
            @if($vendor->business_address)
            <div class="data-row">
                <strong>Dirección del Negocio:</strong> {{ $vendor->business_address }}
            </div>
            @endif
            @if($vendor->business_description)
            <div class="data-row">
                <strong>Descripción del Negocio:</strong><br>
                {{ $vendor->business_description }}
            </div>
            @endif
            <div class="data-row">
                <strong>Fecha de Registro:</strong> {{ $vendor->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="data-row">
                <strong>Estado:</strong> <span style="color: #f59e0b;">Pendiente</span>
            </div>
        </div>

        <p><strong>Acción Requerida:</strong></p>
        <p>Por favor, revisa la solicitud y aprueba o rechaza al vendedor desde el panel de administración.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $adminUrl }}" class="button">Ver Solicitudes de Vendedores</a>
        </div>

        <p>Saludos,<br>
        <strong>Sistema FATMAC</strong></p>
    </div>
</body>
</html>


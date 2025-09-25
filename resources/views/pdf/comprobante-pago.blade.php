<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - FAMASY</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: white;
            padding: 15px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            border: 2px solid #8B4513;
        }

        /* Header con información de la empresa */
        .header {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .company-subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 2px;
        }

        .company-info {
            font-size: 9px;
            color: #666;
        }

        /* Título del documento */
        .document-title {
            text-align: center;
            padding: 8px;
            background: #B8860B;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }

        .document-number {
            text-align: center;
            padding: 5px;
            background: #f8f9fa;
            font-weight: bold;
            color: #B8860B;
            font-size: 12px;
        }

        /* Tabla principal */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 10px;
            border: 1px solid #8B4513;
            vertical-align: top;
            font-size: 10px;
        }

        .info-table .label {
            background: #f0f8ff;
            font-weight: bold;
            width: 25%;
            color: #333;
        }

        .info-table .value {
            background: white;
            color: #333;
        }

        /* Monto destacado */
        .amount-row .value {
            background: #ffe6e6;
            font-weight: bold;
            font-size: 12px;
            color: #333;
        }

        /* Secciones especiales */
        .section-header {
            background: #add8e6;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            color: #333;
            font-size: 10px;
        }

        .observations-section {
            background: #add8e6;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            color: #333;
            font-size: 10px;
        }

        .observations-content {
            padding: 8px 10px;
            background: #f9f9f9;
            border: 1px solid #8B4513;
            font-size: 10px;
        }

        .system-section {
            background: #add8e6;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            color: #333;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 8px;
            background: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }

        .generation-info {
            text-align: center;
            padding: 8px;
            background: #6c757d;
            color: white;
            font-size: 8px;
            line-height: 1.2;
        }

        @page {
            margin: 10mm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header de la empresa -->
        <div class="header">
            <div class="company-name">FAMASY</div>
            <div class="company-subtitle">Finca Agropecuaria Familiar Sostenible</div>
            <div class="company-info">
                Vereda La Esperanza, Pitalito, Huila, Colombia<br>
                NIT: 900.123.456-7 | Tel: +57 318 123 4567
            </div>
        </div>

        <!-- Título del documento -->
        <div class="document-title">
            COMPROBANTE DE PAGO OPERACIONAL
        </div>

        <!-- Número del documento -->
        <div class="document-number">
            N° {{ str_pad($pago->idComGas, 6, '0', STR_PAD_LEFT) }}
        </div>

        <!-- Información básica del pago -->
        <table class="info-table">
            <tr>
                <td class="label">Fecha del Pago:</td>
                <td class="value">{{ date('d/m/Y', strtotime($pago->fecComGas)) }}</td>
            </tr>
            <tr>
                <td class="label">Categoría:</td>
                <td class="value">{{ $pago->catComGas ?? 'Pagos a Proveedores' }}</td>
            </tr>
            <tr>
                <td class="label">Descripción:</td>
                <td class="value">{{ $pago->desComGas ?? 'Sin descripción' }}</td>
            </tr>
            <tr class="amount-row">
                <td class="label">Monto Total:</td>
                <td class="value">${{ number_format($pago->monComGas, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Método de Pago:</td>
                <td class="value">{{ ucfirst($pago->metPagComGas ?? 'No especificado') }}</td>
            </tr>
            @if($pago->docComGas)
            <tr>
                <td class="label">Documento:</td>
                <td class="value">{{ $pago->docComGas }}</td>
            </tr>
            @endif
        </table>

        <!-- Información del proveedor -->
        <div class="section-header">
            INFORMACIÓN DEL PROVEEDOR
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Proveedor:</td>
                <td class="value">{{ $pago->provComGas ?? 'Sin proveedor especificado' }}</td>
            </tr>
        </table>

        <!-- Observaciones si existen -->
        @if($pago->obsComGas)
        <div class="observations-section">
            OBSERVACIONES
        </div>
        <div class="observations-content">
            {{ $pago->obsComGas }}
        </div>
        @endif

        <!-- Información del sistema -->
        <div class="system-section">
            INFORMACIÓN DEL SISTEMA
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Fecha de Registro:</td>
                <td class="value">{{ date('d/m/Y H:i:s', strtotime($pago->created_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Última Modificación:</td>
                <td class="value">{{ date('d/m/Y H:i:s', strtotime($pago->updated_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Transacción:</td>
                <td class="value">PAGO</td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            Autorizado por: Administración FAMASY
        </div>

        <!-- Información de generación -->
        <div class="generation-info">
            Documento generado el {{ date('d/m/Y') }} a las {{ date('H:i:s') }}<br>
            FAMASY - Sistema de Gestión Agropecuaria<br>
            Comprobante de Pago N° {{ str_pad($pago->idComGas, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Cuenta Pendiente - FAMASY</title>
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

        /* Sección de estado especial */
        .status-section {
            background: #e6f3ff;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            color: #0066cc;
            font-size: 10px;
        }

        .status-content {
            padding: 8px 10px;
            background: #f0f8ff;
            border: 1px solid #8B4513;
            font-size: 10px;
            text-align: center;
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

        /* Estilos para estados */
        .status-vencido { color: #dc3545; font-weight: bold; }
        .status-proximo { color: #fd7e14; font-weight: bold; }
        .status-normal { color: #28a745; }
        .status-pagado { color: #28a745; font-weight: bold; }

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
            COMPROBANTE DE CUENTA {{ strtoupper($cuenta->tipCuePen === 'por_cobrar' ? 'POR COBRAR' : 'POR PAGAR') }}
        </div>

        <!-- Número del documento -->
        <div class="document-number">
            N° {{ str_pad($cuenta->idCuePen, 6, '0', STR_PAD_LEFT) }}
        </div>

        <!-- Información básica de la cuenta -->
        <table class="info-table">
            <tr>
                <td class="label">Fecha de Registro:</td>
                <td class="value">{{ date('d/m/Y', strtotime($cuenta->created_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Cuenta:</td>
                <td class="value">{{ $cuenta->tipCuePen === 'por_cobrar' ? 'Por Cobrar' : 'Por Pagar' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de Vencimiento:</td>
                <td class="value">{{ date('d/m/Y', strtotime($cuenta->fecVencimiento)) }}</td>
            </tr>
            <tr class="amount-row">
                <td class="label">Monto Original:</td>
                <td class="value">${{ number_format($cuenta->montoOriginal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Monto Pagado:</td>
                <td class="value">${{ number_format($cuenta->montoPagado, 2) }}</td>
            </tr>
            <tr class="amount-row">
                <td class="label">Saldo Pendiente:</td>
                <td class="value">${{ number_format($cuenta->montoSaldo, 2) }}</td>
            </tr>
        </table>

        <!-- Información del cliente/proveedor -->
        <div class="section-header">
            INFORMACIÓN DEL {{ strtoupper($cuenta->tipCuePen === 'por_cobrar' ? 'CLIENTE' : 'PROVEEDOR') }}
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nombre:</td>
                <td class="value">{{ $cuenta->cliente_nombre ?? $cuenta->proveedor_nombre ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td class="label">Documento:</td>
                <td class="value">{{ $cuenta->cliente_documento ?? $cuenta->proveedor_documento ?? 'No disponible' }}</td>
            </tr>
            @if($cuenta->cliente_telefono ?? $cuenta->proveedor_telefono)
            <tr>
                <td class="label">Teléfono:</td>
                <td class="value">{{ $cuenta->cliente_telefono ?? $cuenta->proveedor_telefono }}</td>
            </tr>
            @endif
            @if($cuenta->cliente_email ?? $cuenta->proveedor_email)
            <tr>
                <td class="label">Email:</td>
                <td class="value">{{ $cuenta->cliente_email ?? $cuenta->proveedor_email }}</td>
            </tr>
            @endif
        </table>

        <!-- Estado y progreso de pago -->
        <div class="status-section">
            ESTADO DE LA CUENTA
        </div>

        <div class="status-content">
            <table class="info-table">
                <tr>
                    <td class="label">Estado Actual:</td>
                    <td class="value">
                        <span class="
                            @if($cuenta->estCuePen === 'vencido') status-vencido
                            @elseif($diasVencimiento['tipo'] === 'proximo') status-proximo
                            @elseif($cuenta->estCuePen === 'pagado') status-pagado
                            @else status-normal
                            @endif
                        ">
                            {{ strtoupper($cuenta->estCuePen) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Progreso de Pago:</td>
                    <td class="value">{{ $porcentajePago }}% completado</td>
                </tr>
                <tr>
                    <td class="label">Estado de Vencimiento:</td>
                    <td class="value">
                        <span class="
                            @if($diasVencimiento['tipo'] === 'vencido') status-vencido
                            @elseif($diasVencimiento['tipo'] === 'proximo') status-proximo
                            @else status-normal
                            @endif
                        ">
                            {{ $diasVencimiento['texto'] }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Documentos relacionados si existen -->
        @if($cuenta->factura_numero || $cuenta->compra_descripcion)
        <div class="section-header">
            DOCUMENTOS RELACIONADOS
        </div>

        <table class="info-table">
            @if($cuenta->factura_numero)
            <tr>
                <td class="label">Factura:</td>
                <td class="value">#{{ $cuenta->factura_numero }}</td>
            </tr>
            @if($cuenta->factura_total)
            <tr>
                <td class="label">Monto Factura:</td>
                <td class="value">${{ number_format($cuenta->factura_total, 2) }}</td>
            </tr>
            @endif
            @if($cuenta->factura_fecha)
            <tr>
                <td class="label">Fecha Factura:</td>
                <td class="value">{{ date('d/m/Y', strtotime($cuenta->factura_fecha)) }}</td>
            </tr>
            @endif
            @endif
            @if($cuenta->compra_descripcion)
            <tr>
                <td class="label">Descripción:</td>
                <td class="value">{{ $cuenta->compra_descripcion }}</td>
            </tr>
            @endif
            @if($cuenta->compra_categoria)
            <tr>
                <td class="label">Categoría:</td>
                <td class="value">{{ $cuenta->compra_categoria }}</td>
            </tr>
            @endif
            @if($cuenta->compra_proveedor && !$cuenta->proveedor_nombre)
            <tr>
                <td class="label">Proveedor Registrado:</td>
                <td class="value">{{ $cuenta->compra_proveedor }}</td>
            </tr>
            @endif
        </table>
        @endif

        <!-- Información adicional si es cuenta relacionada a compra/gasto -->
        @if($cuenta->idComGasCuePen && !($cuenta->factura_numero || $cuenta->compra_descripcion))
        <div class="section-header">
            INFORMACIÓN ADICIONAL
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Referencia:</td>
                <td class="value">Compra/Gasto #{{ $cuenta->idComGasCuePen }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Documento:</td>
                <td class="value">{{ ucfirst($cuenta->tipCuePen === 'por_cobrar' ? 'Cuenta por Cobrar' : 'Cuenta por Pagar') }}</td>
            </tr>
        </table>
        @endif

        <!-- Información del sistema -->
        <div class="system-section">
            INFORMACIÓN DEL SISTEMA
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Fecha de Registro:</td>
                <td class="value">{{ date('d/m/Y H:i:s', strtotime($cuenta->created_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Última Modificación:</td>
                <td class="value">{{ date('d/m/Y H:i:s', strtotime($cuenta->updated_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Transacción:</td>
                <td class="value">{{ strtoupper($cuenta->tipCuePen === 'por_cobrar' ? 'CUENTA POR COBRAR' : 'CUENTA POR PAGAR') }}</td>
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
            Comprobante de Cuenta {{ $cuenta->tipCuePen === 'por_cobrar' ? 'por Cobrar' : 'por Pagar' }} N° {{ str_pad($cuenta->idCuePen, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Gasto - {{ str_pad($gasto->idComGas, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
        }
        
        @page {
            margin: 15mm;
            size: A4;
        }
        
        .header {
            text-align: center;
            border: 2px solid #666;
            padding: 15px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2d5016;
            margin-bottom: 5px;
        }
        
        .company-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .company-info {
            font-size: 10px;
            color: #666;
        }
        
        .document-title {
            background: #d4af37;
            color: #8b4513;
            font-size: 14px;
            font-weight: bold;
            padding: 8px;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .document-number {
            color: #dc2626;
            font-size: 12px;
            font-weight: bold;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .info-table td {
            border: 1px solid #666;
            padding: 8px;
            vertical-align: top;
        }
        
        .label-cell {
            background: #f0f0f0;
            font-weight: bold;
            width: 25%;
            font-size: 10px;
        }
        
        .value-cell {
            background: white;
            width: 75%;
        }
        
        .amount-cell {
            background: #ffe4e6;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
        }
        
        .section-header {
            background: #b0c4de;
            color: #1e40af;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .signature-section {
            text-align: center;
            margin: 30px 0 20px 0;
            padding: 20px 0;
            border-top: 1px solid #666;
        }
        
        .footer {
            background: #6c757d;
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 9px;
            margin-top: 20px;
        }
        
        .footer-line {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">FAMASY</div>
        <div class="company-subtitle">Finca Agropecuaria Familiar Sostenible</div>
        <div class="company-info">Vereda La Esperanza, Pitalito, Huila, Colombia</div>
        <div class="company-info">NIT: {{ $datosGranja['nit'] ?? '900.123.456-7' }} | Tel: {{ $datosGranja['telefono'] ?? '+57 318 123 4567' }}</div>
        
        <div class="document-title">
            COMPROBANTE DE GASTO OPERACIONAL
        </div>
        <div class="document-number">
            N° {{ str_pad($gasto->idComGas, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label-cell">Fecha del Gasto:</td>
            <td class="value-cell">{{ date('d/m/Y', strtotime($gasto->fecComGas)) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Categoría:</td>
            <td class="value-cell">{{ $gasto->catComGas ?? 'Sin categoría' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Descripción:</td>
            <td class="value-cell"><strong>{{ $gasto->desComGas ?? 'Sin descripción' }}</strong></td>
        </tr>
        <tr>
            <td class="label-cell">Monto Total:</td>
            <td class="amount-cell">${{ number_format($gasto->monComGas, 2) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Método de Pago:</td>
            <td class="value-cell">{{ ucfirst(str_replace('_', ' ', $gasto->metPagComGas ?? 'Sin método')) }}</td>
        </tr>
        @if($gasto->docComGas)
        <tr>
            <td class="label-cell">Documento:</td>
            <td class="value-cell">{{ $gasto->docComGas }}</td>
        </tr>
        @endif
    </table>

    <table class="info-table">
        <tr>
            <td class="section-header" colspan="2">INFORMACIÓN DEL PROVEEDOR</td>
        </tr>
        <tr>
            <td class="label-cell">Proveedor:</td>
            <td class="value-cell">{{ $gasto->provComGas ?? 'Sin proveedor' }}</td>
        </tr>
        @if($gasto->nitProve)
        <tr>
            <td class="label-cell">NIT/Documento:</td>
            <td class="value-cell">{{ $gasto->nitProve }}</td>
        </tr>
        @endif
    </table>

    @if($gasto->obsComGas)
    <table class="info-table">
        <tr>
            <td class="section-header" colspan="2">OBSERVACIONES</td>
        </tr>
        <tr>
            <td colspan="2" style="background: #fffbeb; padding: 10px;">
                {{ $gasto->obsComGas }}
            </td>
        </tr>
    </table>
    @endif

    <table class="info-table">
        <tr>
            <td class="section-header" colspan="2">INFORMACIÓN DEL SISTEMA</td>
        </tr>
        <tr>
            <td class="label-cell">Fecha de Registro:</td>
            <td class="value-cell">{{ date('d/m/Y H:i', strtotime($gasto->created_at)) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Última Modificación:</td>
            <td class="value-cell">{{ date('d/m/Y H:i', strtotime($gasto->updated_at)) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Tipo de Transacción:</td>
            <td class="value-cell">{{ strtoupper($gasto->tipComGas ?? 'GASTO') }}</td>
        </tr>
    </table>

    <div class="signature-section">
        <strong>Autorizado por: Administración FAMASY</strong>
    </div>

    <div class="footer">
        <div class="footer-line">Documento generado el {{ date('d/m/Y') }} a las {{ date('H:i') }}</div>
        <div class="footer-line">FAMASY - Sistema de Gestión Agropecuaria</div>
        <div class="footer-line">Comprobante de Gasto N° {{ $gasto->idComGas }}</div>
    </div>
</body>
</html>
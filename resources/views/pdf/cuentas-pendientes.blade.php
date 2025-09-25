<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .report-title { font-size: 16px; margin: 10px 0; color: #d97706; }
        .date-info { font-size: 12px; color: #666; margin: 5px 0; }
        
        .status-cards { display: table; width: 100%; margin: 20px 0; }
        .card { display: table-cell; width: 33.33%; padding: 15px; margin: 10px; border: 1px solid #ddd; text-align: center; }
        .card-title { font-size: 12px; margin-bottom: 8px; font-weight: bold; }
        .card-count { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .card-amount { font-size: 14px; }
        .overdue { color: #dc2626; border-color: #dc2626; }
        .upcoming { color: #d97706; border-color: #d97706; }
        .current { color: #16a34a; border-color: #16a34a; }
        
        .accounts-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .accounts-table th, .accounts-table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            font-size: 11px;
        }
        .accounts-table th { background-color: #f5f5f5; font-weight: bold; }
        .money { text-align: right; font-family: monospace; }
        .total-line { border-top: 2px solid #000; font-weight: bold; }
        
        .recommendations { margin: 30px 0; padding: 20px; background-color: #fff7ed; border-left: 4px solid #d97706; }
        .recommendations h3 { color: #d97706; margin-top: 0; }
        .recommendations ul { margin: 10px 0; padding-left: 20px; }
        .recommendations li { margin: 5px 0; }
        
        .footer { margin-top: 40px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $granja }}</div>
        <div class="report-title">{{ $titulo }}</div>
        <div class="date-info">Al {{ $periodo }}</div>
        <div class="date-info">Generado: {{ $fecha_generacion }} por {{ $usuario }}</div>
    </div>

    <!-- Resumen de Estados -->
    <div class="status-cards">
        <div class="card overdue">
            <div class="card-title">CUENTAS VENCIDAS</div>
            <div class="card-count">{{ $analisis_vencimientos['vencidas']['cantidad'] ?? 0 }}</div>
            <div class="card-amount">${{ number_format($analisis_vencimientos['vencidas']['monto'] ?? 0, 2) }}</div>
        </div>
        <div class="card upcoming">
            <div class="card-title">PRÓXIMAS A VENCER (30 días)</div>
            <div class="card-count">{{ $analisis_vencimientos['proximas_30_dias']['cantidad'] ?? 0 }}</div>
            <div class="card-amount">${{ number_format($analisis_vencimientos['proximas_30_dias']['monto'] ?? 0, 2) }}</div>
        </div>
        <div class="card current">
            <div class="card-title">FUTURAS</div>
            <div class="card-count">{{ $analisis_vencimientos['futuras']['cantidad'] ?? 0 }}</div>
            <div class="card-amount">${{ number_format($analisis_vencimientos['futuras']['monto'] ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- Detalle de Cuentas Vencidas -->
    @if($cuentas_pendientes['vencidas']->count() > 0)
    <h3 style="color: #dc2626;">Cuentas Vencidas - Acción Inmediata Requerida</h3>
    <table class="accounts-table">
        <thead>
            <tr>
                <th width="25%">Tipo</th>
                <th width="25%">Cantidad</th>
                <th width="25%">Monto Total</th>
                <th width="25%">Monto Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuentas_pendientes['vencidas'] as $vencida)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $vencida->tipCuePen)) }}</td>
                <td style="text-align: center;">{{ $vencida->cantidad }}</td>
                <td class="money" style="color: #dc2626;">
                    ${{ number_format($vencida->total, 2) }}
                </td>
                <td class="money">
                    ${{ number_format($vencida->cantidad > 0 ? $vencida->total / $vencida->cantidad : 0, 2) }}
                </td>
            </tr>
            @endforeach
            <tr class="total-line">
                <td colspan="2"><strong>TOTAL VENCIDO</strong></td>
                <td class="money" style="color: #dc2626;">
                    <strong>${{ number_format($cuentas_pendientes['total_vencidas'], 2) }}</strong>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Detalle de Próximas a Vencer -->
    @if($cuentas_pendientes['proximas_vencer']->count() > 0)
    <h3 style="color: #d97706;">Cuentas Próximas a Vencer (30 días)</h3>
    <table class="accounts-table">
        <thead>
            <tr>
                <th width="25%">Tipo</th>
                <th width="25%">Cantidad</th>
                <th width="25%">Monto Total</th>
                <th width="25%">Monto Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuentas_pendientes['proximas_vencer'] as $proxima)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $proxima->tipCuePen)) }}</td>
                <td style="text-align: center;">{{ $proxima->cantidad }}</td>
                <td class="money" style="color: #d97706;">
                    ${{ number_format($proxima->total, 2) }}
                </td>
                <td class="money">
                    ${{ number_format($proxima->cantidad > 0 ? $proxima->total / $proxima->cantidad : 0, 2) }}
                </td>
            </tr>
            @endforeach
            <tr class="total-line">
                <td colspan="2"><strong>TOTAL PRÓXIMO A VENCER</strong></td>
                <td class="money" style="color: #d97706;">
                    <strong>${{ number_format($cuentas_pendientes['total_proximas'], 2) }}</strong>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Resumen Total -->
    <div style="margin: 30px 0; padding: 20px; background-color: #f3f4f6; border-radius: 8px;">
        <h3 style="text-align: center; margin-bottom: 20px;">Resumen General</h3>
        <table class="accounts-table">
            <tbody>
                <tr>
                    <td><strong>Total Cuentas Vencidas</strong></td>
                    <td class="money" style="color: #dc2626;">
                        <strong>${{ number_format($cuentas_pendientes['total_vencidas'], 2) }}</strong>
                    </td>
                </tr>
                <tr>
                    <td><strong>Total Próximas a Vencer</strong></td>
                    <td class="money" style="color: #d97706;">
                        <strong>${{ number_format($cuentas_pendientes['total_proximas'], 2) }}</strong>
                    </td>
                </tr>
                <tr class="total-line">
                    <td><strong>TOTAL CUENTAS PENDIENTES</strong></td>
                    <td class="money">
                        <strong>${{ number_format($cuentas_pendientes['total_vencidas'] + $cuentas_pendientes['total_proximas'], 2) }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Recomendaciones -->
    @if(!empty($recomendaciones))
    <div class="recommendations">
        <h3>Recomendaciones de Cobranza</h3>
        <ul>
            @foreach($recomendaciones as $recomendacion)
            <li>{{ $recomendacion }}</li>
            @endforeach
        </ul>
        
        <div style="margin-top: 15px;">
            <strong>Acciones Prioritarias:</strong>
            <ul>
                @if($cuentas_pendientes['total_vencidas'] > 0)
                <li style="color: #dc2626;">Contactar inmediatamente a deudores con cuentas vencidas</li>
                <li>Evaluar políticas de crédito y condiciones de pago</li>
                @endif
                @if($cuentas_pendientes['total_proximas'] > 0)
                <li style="color: #d97706;">Enviar recordatorios de pago a cuentas próximas a vencer</li>
                @endif
                <li>Revisar y actualizar políticas de cobranza</li>
                <li>Considerar incentivos por pronto pago</li>
            </ul>
        </div>
    </div>
    @endif

    <!-- Indicadores de Gestión -->
    <div style="margin: 30px 0; padding: 20px; background-color: #f0f9ff; border-radius: 8px;">
        <h3 style="color: #0369a1;">Indicadores de Gestión de Cartera</h3>
        <table class="accounts-table">
            <tbody>
                <tr>
                    <td>Porcentaje Vencido</td>
                    <td class="money">
                        {{ $cuentas_pendientes['total_vencidas'] + $cuentas_pendientes['total_proximas'] > 0 ? number_format(($cuentas_pendientes['total_vencidas'] / ($cuentas_pendientes['total_vencidas'] + $cuentas_pendientes['total_proximas'])) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Riesgo de Liquidez</td>
                    <td class="money" style="color: {{ $cuentas_pendientes['total_vencidas'] > $cuentas_pendientes['total_proximas'] ? '#dc2626' : '#16a34a' }};">
                        {{ $cuentas_pendientes['total_vencidas'] > $cuentas_pendientes['total_proximas'] ? 'Alto' : 'Bajo' }}
                    </td>
                </tr>
                <tr>
                    <td>Total Cuentas Activas</td>
                    <td class="money">
                        {{ ($analisis_vencimientos['vencidas']['cantidad'] ?? 0) + ($analisis_vencimientos['proximas_30_dias']['cantidad'] ?? 0) + ($analisis_vencimientos['futuras']['cantidad'] ?? 0) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>{{ $granja }} - Sistema de Gestión de Cartera</p>
        <p>Reporte generado el {{ $fecha_generacion }}</p>
        <p><strong>Nota:</strong> Este reporte debe revisarse semanalmente para mantener un control efectivo de la cartera</p>
    </div>
</body>
</html>
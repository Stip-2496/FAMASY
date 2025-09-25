<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura FAC-{{ $factura->idFac }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-cell { display: table-cell; width: 50%; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FAMASY - Sistema Agropecuario</h1>
        <h2>Factura de Venta</h2>
        <p>FAC-{{ $factura->idFac }}</p>
    </div>

    <div class="info-grid">
        <div class="info-cell">
            <h3>Información del Cliente</h3>
            <p><strong>Nombre:</strong> {{ $factura->nomCliFac }}</p>
            <p><strong>Documento:</strong> {{ $factura->tipDocCliFac }}: {{ $factura->docCliFac }}</p>
        </div>
        <div class="info-cell">
            <h3>Información de la Factura</h3>
            <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($factura->fecFac)) }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($factura->estFac) }}</p>
            <p><strong>Método de Pago:</strong> {{ $factura->metPagFac }}</p>
        </div>
    </div>

    <h3>Detalles de Productos/Servicios</h3>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td>{{ $detalle->conceptoDet }}</td>
                <td>{{ $detalle->cantidadDet }}</td>
                <td>${{ number_format($detalle->precioUnitDet, 2) }}</td>
                <td>${{ number_format($detalle->subtotalDet, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">Subtotal</td>
                <td>${{ number_format($factura->subtotalFac, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="3">IVA</td>
                <td>${{ number_format($factura->ivaFac, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="3">TOTAL</td>
                <td>${{ number_format($factura->totFac, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($factura->obsFac)
    <h3>Observaciones</h3>
    <p>{{ $factura->obsFac }}</p>
    @endif
</body>
</html>
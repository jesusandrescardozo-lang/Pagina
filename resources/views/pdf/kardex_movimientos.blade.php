<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos Kardex</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 20px; }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 16px; font-weight: bold; color: #065f46; }
        .subtitle { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background-color: #ecfdf5; color: #047857; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .text-in { color: #059669; font-weight: bold; }
        .text-out { color: #dc2626; font-weight: bold; }
        .footer { text-align: right; font-size: 9px; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LIBRO KARDEX &mdash; CONTROL DE ENTRADAS Y SALIDAS</div>
        <div class="subtitle">Trazabilidad oficial de donaciones, consumos y mermas en la red de albergues</div>
        <div style="font-size: 10px; color: #555; margin-top: 4px;">
            Fecha: {{ $fecha }} 
            @if($shelter) &bull; Refugio: <strong>{{ $shelter->nombre }}</strong> @endif
            @if($supply) &bull; Insumo: <strong>{{ $supply->nombre }}</strong> @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Refugio</th>
                <th>Insumo</th>
                <th>Tipo de Movimiento</th>
                <th>Lote</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Resultante</th>
                <th>Referencia / Motivo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
            <tr>
                <td>{{ $m->fecha_movimiento->format('d/m/Y H:i') }}</td>
                <td>{{ $m->shelter->nombre }}</td>
                <td>{{ $m->supply->nombre }}</td>
                <td>{{ $m->tipo_badge['text'] }}</td>
                <td>{{ $m->batch ? $m->batch->numero_lote : '—' }}</td>
                <td class="{{ $m->es_entrada ? 'text-in' : 'text-out' }}">
                    {{ $m->es_entrada ? '+' : '-' }}{{ $m->cantidad }} {{ $m->supply->unidad_medida }}
                </td>
                <td>{{ $m->stock_anterior }}</td>
                <td><strong>{{ $m->stock_nuevo }}</strong></td>
                <td>{{ $m->documento_referencia ?? $m->motivo ?? '—' }}</td>
                <td>{{ $m->user ? $m->user->name : 'Sistema' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; color: #888;">No se registraron movimientos en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado por Sistema NUTRILINK &bull; Bitácora Inmutable de Almacén
    </div>
</body>
</html>

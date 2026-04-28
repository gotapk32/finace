<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Gastos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; font-size: 16px; margin-top: 20px; text-align: right; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Gastos - {{ date('M Y') }}</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Pagador</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date }}</td>
                <td>{{ $expense->name }}</td>
                <td>{{ $expense->category->name }}</td>
                <td>{{ $expense->payer }}</td>
                <td>${{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL DEL MES: ${{ number_format($total, 2) }}
    </div>
</body>
</html>

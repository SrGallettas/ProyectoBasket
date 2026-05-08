<h1>Nueva solicitud de pedido</h1>

<p>Se ha recibido una nueva solicitud desde la tienda pública de Bellreguard CB.</p>

<h2>Datos del cliente</h2>

<ul>
    <li><strong>Nombre:</strong> {{ $cliente['nombre'] }}</li>
    <li><strong>Apellidos:</strong> {{ $cliente['apellidos'] }}</li>
    <li><strong>Teléfono:</strong> {{ $cliente['telefono'] }}</li>
    <li><strong>Correo:</strong> {{ $cliente['correo'] }}</li>
    <li><strong>Notas:</strong> {{ $cliente['notas'] ?: 'Sin observaciones.' }}</li>
</ul>

<h2>Productos solicitados</h2>

<table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th align="left">Producto</th>
            <th align="left">Talla</th>
            <th align="left">Cantidad</th>
            <th align="left">Precio</th>
            <th align="left">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cartItems as $item)
            <tr>
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['talla'] }}</td>
                <td>{{ $item['cantidad'] }}</td>
                <td>{{ number_format($item['precio'], 2, ',', '.') }} €</td>
                <td>{{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }} €</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p><strong>Total de unidades:</strong> {{ $cartSummary['units'] }}</p>
<p><strong>Subtotal orientativo:</strong> {{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</p>

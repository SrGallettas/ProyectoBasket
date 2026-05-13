<h1>{{ __('admin.emails.orders.title') }}</h1>

<p>{{ __('admin.emails.orders.intro') }}</p>

<h2>{{ __('admin.emails.orders.customer') }}</h2>

<ul>
    <li><strong>{{ __('admin.emails.orders.fields.name') }}:</strong> {{ $cliente['nombre'] }}</li>
    <li><strong>{{ __('admin.emails.orders.fields.surname') }}:</strong> {{ $cliente['apellidos'] }}</li>
    <li><strong>{{ __('admin.emails.orders.fields.phone') }}:</strong> {{ $cliente['telefono'] }}</li>
    <li><strong>{{ __('admin.emails.orders.fields.email') }}:</strong> {{ $cliente['correo'] }}</li>
    <li><strong>{{ __('admin.emails.orders.fields.notes') }}:</strong> {{ $cliente['notas'] ?: __('admin.emails.orders.notes_fallback') }}</li>
</ul>

<h2>{{ __('admin.emails.orders.products') }}</h2>

<table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th align="left">{{ __('admin.emails.orders.fields.product') }}</th>
            <th align="left">{{ __('admin.emails.orders.fields.size') }}</th>
            <th align="left">{{ __('admin.emails.orders.fields.quantity') }}</th>
            <th align="left">{{ __('admin.emails.orders.fields.price') }}</th>
            <th align="left">{{ __('admin.emails.orders.fields.amount') }}</th>
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

<p><strong>{{ __('admin.emails.orders.fields.total_units') }}:</strong> {{ $cartSummary['units'] }}</p>
<p><strong>{{ __('admin.emails.orders.fields.subtotal') }}:</strong> {{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</p>

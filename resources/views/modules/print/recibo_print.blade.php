@extends('layouts.ticket')
@section('title', 'Recibo de Pago')
@section('content')

    <div id="header">

        <div class="datos-institucion">
            <img src={{ asset('img/logo-maluz.jpg') }} width="130px">
            <div class="nombre">{{ $pos_order->tienda->nombre }}</div>
            <div class="direccion">{{ $pos_order->tienda->direccion }}</div>
        </div>

        <table id="datos-recibo">
            <tbody>
                <tr>
                    <td>N° Recibo:</td>
                    <td>{{ $pos_order->serie }}-{{ $pos_order->order_number }}</td>

                </tr>
                <tr>
                    <td>Fecha :</td>
                    <td>{{ $pos_order->order_date }}</td>

                </tr>
                <tr>
                    <td>
                        Cliente:
                    </td>
                    <td> {{ $pos_order->cliente->nombre ?? 'Consumidor Final' }}</td>

                </tr>
                

            </tbody>
        </table>



    </div>
    <div id="body">
        <table class="table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="col1">can</th>
                    <th class="col2">producto</th>
                    <th class="col3">Monto</th>
                </tr>
            </thead>
            <tbody class="cuerpo">
                @foreach ($pos_order->orderLines as $line)
                    
                    <tr>
                        <td class="col1">{{$line->quantity}}</td>
                        <td class="col2">{{ $line->producto->nombre }}</td>
                        <td class="col3">{{ number_format($line->price, 2) }}</td>
                    </tr>
                @endforeach
                
            </tbody>
        </table>

        <div class="total">
            <div>Total  
                <span class="totalprecio">
                    {{ number_format($pos_order->total_amount, 2) }}
                </span>
            </div>
        </div>
      
    </div>
    <div id="footer">
        <div class="mensajeFooter1">{!! nl2br(e($pos_order->tienda->ticket_nota)) !!}</div>
    </div>
@endsection

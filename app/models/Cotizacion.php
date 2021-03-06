<?php

class Cotizacion extends Eloquent
{
    protected $table = 'cotizaciones';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function cliente()
    {
        return $this->belongsTo('Cliente');
    }

    public function items()
    {
        return $this->hasMany('CotizacionItem');
    }

    /**
     * Devuelve la variable de nombre con el nombre que contiene $dato
     * @param string $dato Nombre de la variable que devolverá puede tomar uno de
     * los siguientes valores: excento, gravado, iva ó total.
     * @return integer Total de la variable con el nombre que contiene $dato
     */
    public function calcularTotal($dato)
    {
        $totales = array('excento' => 0, 'gravado' => 0, 'iva' => 0, 'total' => 0);

        foreach ($this->items as $item) {
            if(is_numeric($item->iva))
            {
                $totales['gravado'] += $item->cantidad * $item->precio;
                $totales['iva'] += $item->cantidad * ($item->precio * ($item->iva / 100));
            } else {
                $totales['excento'] += $item->cantidad * $item->precio;
            }
        }

        $totales['total'] = $totales['excento'] + $totales['gravado'] + $totales['iva'];

        return $totales[$dato];

    } #calcularTotales

    public function alCarrito()
    {
        $carrito = array();

        if (Session::has('carrito')) {
            $carrito = Session::get('carrito');
        }

        foreach($this->items as $item)
        {
            $carrito[$item->articulo->id] = array('articulo' => $item->articulo, 'cantidad' => $item->cantidad);
        }

        Session::put('carrito', $carrito);
        Session::put('cliente', $this->cliente);

    } #alCarrito

} #Cotizacion

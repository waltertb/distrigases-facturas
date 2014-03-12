<?php

class FacturaController extends BaseController
{
    public function getIndex()
    {
        return Redirect::to('facturas/listado');
    }

    public function getListado()
    {
        $facturas = Factura::orderBy('id', 'desc')->paginate(2);

        return View::make('facturas.listado')
            ->with(compact('facturas'));
    }

    public function postNueva()
    {
        try
        {
            if(!Session::has('cliente'))
            {
                Session::flash('mensajeError', 'Debes seleccionar un cliente para continuar.');

                return Redirect::to('carrito');
            }

            $input = Input::all();
            $factura = new Factura();
            $factura->cliente_id = Session::get('cliente')->id;
            $factura->vencimiento = $input['vencimiento'];
            $factura->pedido = $input['pedido'];
            $factura->estado = 'pendiente';
            $factura->notas = $input['notas'];
            $factura->user_id = Auth::user()->id;
            $factura->save();

            if(self::guardarItems($factura->id) === false)
            {
                $factura->delete();
                Session::flash('mensajeError', 'No fue posible guardar la factura.');

                return Redirect::to('carrito');
            }

            Session::forget('carrito');
            Session::forget('cliente');
            Session::flash('mensajeOk', 'Has creado la factura '. $factura->id .' con éxito.');

            return Redirect::to('facturas/filtro-por-id/'. $factura->id);

        } catch (Exception $e) {

            Session::flash('mensajeError', 'No fue posible guardar la factura.');

            return Redirect::to('carrito');
        }

    } #postNueva

    private function guardarItems($idFactura)
    {
        try
        {
            if(Session::has('carrito'))
            {
                $carrito = Session::get('carrito');

                if(empty($carrito))
                {
                    $filasAfectadas = FacturaItem::where('factura_id', '=', $idFactura)->delete();

                    return false;
                }

                foreach ($carrito as $item) {
                    $fi = new FacturaItem();
                    $fi->factura_id = $idFactura;
                    $fi->articulo_id = $item['articulo']->id;
                    $fi->cantidad = $item['cantidad'];
                    $fi->precio = $item['articulo']->precio;
                    $fi->iva = $item['articulo']->iva;
                    $fi->save();
                }

                return true;
            }

        } catch (Exception $e) {

            $filasAfectadas = FacturaItem::where('factura_id', '=', $idFactura)->delete();

            return false;
        }

    } #guardarItems

    public function getFiltroPorId($idFactura=0)
    {
        try
        {
            if($idFactura == 0)
            {
                $idFactura = Input::get('idFactura');
            }

            $facturas = Factura::where('id', '=', $idFactura)->orderBy('id', 'desc')->paginate(1);
            Session::flash('mensajeOk', 'Se ha realizado la busqueda de la factura '. $idFactura);

            return View::make('facturas.listado')
                ->with(compact('facturas'));

        } catch (Exception $e) {

            Session::flash('mensajeError', 'Ha ocurrido un error al intentar mostrar '. $idFactura);

            return Redirect::to('facturas');
        }

    } #getfiltroPorId

} #FacturaController

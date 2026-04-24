<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\OrderRequest;
use App\Events\ProductOutOfStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Crea un nuevo pedido y gestiona el stock.
     */
    public function store(OrderRequest $request)
    {
        // 1. Obtenemos los datos ya validados por el Escudo (OrderRequest)
        $data = $request->validated();

        try {
            // 2. Iniciamos una transacción: o se guarda todo, o nada.
            return DB::transaction(function () use ($data) {
                $total = 0;
                $orderItems = [];

                // 3. Procesamos cada producto del carrito
                foreach ($data['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    // Validación de negocio: ¿Hay stock suficiente?
                    if ($product->stock < $item['quantity']) {
                        // Lanzamos excepción para que la transacción haga rollback
                        throw new \Exception("Stock insuficiente para el producto: {$product->name}");
                    }

                    // Calculamos totales
                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    // Preparamos los datos para la tabla pivote (order_product)
                    $orderItems[$product->id] = [
                        'quantity'   => $item['quantity'],
                        'unit_price' => $product->price
                    ];

                    // 4. Actualizamos el stock en la base de datos
                    $product->decrement('stock', $item['quantity']);

                    // 5. Disparamos el Evento si el stock se agotó
                    if ($product->stock <= 0) {
                        event(new ProductOutOfStock($product));
                    }
                }

                // 6. Creamos la cabecera del Pedido
                $order = Order::create([
                    'user_id'          => Auth::id(),
                    'total'            => $total,
                    'status'           => 'pending',
                    'contact_email'    => $data['contact_email'],
                    'shipping_address' => $data['shipping_address'],
                ]);

                // 7. Guardamos la relación en la tabla pivote
                $order->products()->attach($orderItems);

                // 8. Respuesta de éxito
                return response()->json([
                    'status'  => true,
                    'message' => 'Pedido realizado con éxito',
                    'data'    => $order->load('products')
                ], Response::HTTP_CREATED);
            });

        } catch (\Exception $e) {
            // Si algo falló (como el stock), devolvemos el error controlado
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Lista los pedidos del usuario autenticado.
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $orders
        ], Response::HTTP_OK);
    }

    public function allOrders()
{
    $orders = Order::with(['user', 'products'])
        ->orderBy('created_at', 'desc')
        ->get();

    // 2. Respondemos con éxito
    return response()->json([
        'status' => true,
        'message' => 'Listado de todos los pedidos recuperado',
        'count'  => $orders->count(),
        'data'   => $orders
    ], Response::HTTP_OK);
}

    public function updateStatus(\Illuminate\Http\Request $request, \App\Models\Order $order)
    {
        // Validamos que el estado solo pueda ser uno de estos 3
        $data = $request->validate([
            'status' => 'required|string|in:pending,shipped,delivered'
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Estado del pedido actualizado a: ' . $data['status'],
            'data' => $order
        ], \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
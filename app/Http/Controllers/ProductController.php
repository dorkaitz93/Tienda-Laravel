<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Events\ProductOutOfStock;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category:id,name')->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(10);

        return response()->json($products, Response::HTTP_OK);
    }

    public function store(ProductRequest $request)
    {
        // El Observer se encargará de generar el SLUG y el SKU automáticamente
        $product = Product::create($request->validated());

        return response()->json([
            "message" => "Producto creado con éxito",
            "data" => $product
        ], Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();


        //stock cambia 
        if ($product->wasChanged('stock') && $product->stock <= 0) {
            event(new ProductOutOfStock($product));
        }

        return response()->json([
            "message" => "Producto actualizado con éxito",
            "data" => $product
        ], Response::HTTP_OK);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            "message" => "Producto eliminado correctamente"
        ], Response::HTTP_OK);
    }
}
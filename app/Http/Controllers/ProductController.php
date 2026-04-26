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

        $product = Product::create($request->validated());

        return response()->json([
            "message" => "Producto creado con éxito",
            "data" => $product
        ], Response::HTTP_CREATED);
    }

    public function show($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'message' => "el producto con ID {$id} no existe en nuestro catálogo",
        ], Response::HTTP_NOT_FOUND);
    }

    return response()->json($product->load('category'), Response::HTTP_OK);
}

    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => "el producto con ID {$id} no existe en nuestro catálogo"
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        $product->update($data);
       
        if ($product->wasChanged('stock') && $product->stock <= 0) {
            event(new ProductOutOfStock($product));
        }

        return response()->json([
            "message" => "Producto actualizado con éxito",
            "data" => $product
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {   
        $product = Product::find($id);
        
        if(!$product){
            return response()->json([
                'message' => "el producto con ID {$id} no existe en nuestro catálogo"
            ], Response::HTTP_NOT_FOUND);
        }
        $product->delete();
        return response()->json([
            "message" => "Producto eliminado correctamente"
        ], Response::HTTP_OK);
    }
}
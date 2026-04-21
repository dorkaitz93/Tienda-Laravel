<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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

    $products = $query->paginate(10); // 10 productos por página

    return response()->json($products, Response::HTTP_OK);
    }

    
     //heredamos de ApiFormRequest.
     
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        
        // Generamos slug automático
        $data['slug'] = Str::slug($data['name']) . '-' . rand(1000, 9999);

        $product = Product::create($data);

        return response()->json([
            "message" => "Producto creado con éxito",
            "data" => $product
        ], Response::HTTP_CREATED);
    }

    
     // Usamos Route Model Bindind.
    
    public function show(Product $product)
    {
        // Cargamos la relación antes de enviarlo
        return response()->json($product->load('category'));
    }

    
     // UPDATE: Editar un producto.
    
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        
        //actualizamos slug
        if ($request->has('name')) {
            $data['slug'] = Str::slug($data['name']) . '-' . $product->id;
        }

        $product->update($data);

        return response()->json([
            "message" => "Producto actualizado",
            "data" => $product
        ]);
    }

    
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(["message" => "Producto eliminado correctamente"]);
    }
}

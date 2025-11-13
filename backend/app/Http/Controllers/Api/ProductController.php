<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return \App\Http\Resources\ProductResource
     */
    public function index()
    {
        $products = Product::latest()->paginate(5);
        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return \App\Http\Resources\ProductResource
     */
    public function store(Request $request): ProductResource|\Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('products', $image->hashName());

        $product = Product::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $product);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return \App\Http\Resources\ProductResource
     */
    public function show($id)
    {
        $product = Product::find($id);
        return new ProductResource(true, 'Detail Data Product!', $product);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return \App\Http\Resources\ProductResource
     */
    public function update(Request $request, $id): ProductResource|\Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);

        if ($request->hasFile('image')) {

            Storage::delete('products/' . basename($product->image));

            $image = $request->file('image');
            $image->storeAs('products', $image->hashName());

            $product->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

        } else {

            $product->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);
        }

        return new ProductResource(true, 'Data Product Berhasil Diubah!', $product);
    }

    /**
     * destroy
     *
     * @param  mixed $id
     * @return \App\Http\Resources\ProductResource
     */
    public function destroy($id)
    {

        $product = Product::find($id);
        Storage::delete('products/' . basename($product->image));
        $product->delete();

        return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
    }
}
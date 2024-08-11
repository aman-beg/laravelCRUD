<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\Product;

class ProductController extends Controller
{
    // show product page
    public function index(){
        $products = Product::orderBy('created_at','DESC')->get();
        return view("products.list",["products"=>$products]); // passing products to list.blade.php
    }

    // create product 
    public function create()
    {
        return view("products.create");
    }
    // store product 
    public function store(Request $request)
    {
        //validation
        $rules = [
            "name"=> "required|min:5",
            "sku"=> "required|min:3",
            "price"=> "required|numeric",
        ];
        if($request->image!=""){
            $rules["image"] = "image";
        }

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        //insert product in DB
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        //store image
        if($request->image!=""){
            //get image & ext
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();

            // create unique name
            $imgname = time().'.'.$ext;

            // save in public dir
            $image->move(public_path('uploads/products'), $imgname);

            // save image name in DB
            $product->image = $imgname;
            $product->save();
        }
        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }
    // show edit product page
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit',[
            'product'=> $product
        ]);
    }

    // update product
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);

        //validation
        $rules = [
            "name" => "required|min:5",
            "sku" => "required|min:3",
            "price" => "required|numeric",
        ];
        if ($request->image != "") {
            $rules["image"] = "image";
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('products.edit',$product->id)->withInput()->withErrors($validator);
        }

        //update product in DB
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        //store image
        if ($request->image != "") {
            // delete old image
            File::delete(public_path('uploads/products/'.$product->image));

            //get image & ext
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();

            // create unique name
            $imgname = time() . '.' . $ext;

            // save in public dir
            $image->move(public_path('uploads/products'), $imgname);

            // save image name in DB
            $product->image = $imgname;
            $product->save();
        }
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // delete product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        //delete image
        File::delete(public_path('uploads/products/'.$product->image));

        //delete from DB
        $product->delete();

        return redirect()->route('products.index')->with('success','Product deleted successfully.');
    }
}

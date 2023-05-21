<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ProductController extends Controller
{
    public function getProducts()
    {
        
        $shop = Shop::first();

        $res = shopify_api_call(
            $shop->access_token,
            $shop->shop_url,
            '/admin/api/2022-04/products.json'
        );
        $products = (json_decode($res['response']));
        
        return view('Products.index', compact('products'));
    }

    public function saveDataDB(Request $request)
    {
      

        $shop = Shop::first();

        $res = shopify_api_call(
            $shop->access_token,
            $shop->shop_url,
            '/admin/api/2022-04/products.json'
        );

        $productsData = (json_decode($res['response']));

        foreach($productsData as $key => $product){
            foreach($product as $k => $val){

                $productJSON = new Product();
                $productJSON->name = $val->title;
                $productJSON->price = $val->variants[0]->price ?? 0.00;
                $productJSON->description = $val->body_html;
                $productJSON->status = $val->status;
                $productJSON->tags = $val->tags;
                $productJSON->images = $val->image->src;        
                $productJSON->save();
            }
            
        }

        $products = Product::paginate(5);

        return view('Products.dataFromDB' , compact('products'));

      
    }

    public function viewDataFromDB()
    {
        
        $products = Product::paginate(5);

        dd($products);

        return view('Products.dataFromDB' , compact('products'));
    }
    
}

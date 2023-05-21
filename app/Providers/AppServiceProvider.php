<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shopify\Auth\FileSessionStorage;
use Shopify\Context;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Initilizing Shopify API
        $this->initShopify();
    }
        
    // /**
    //  * initShopify
    //  *
    //  * @return void
    //  */
    public function initShopify()
    {
        Context::initialize(
            env('SHOPIFY_API_KEY'),
            env('SHOPIFY_API_SECRET'),
            env('SHOPIFY_APP_SCOPES'),
            env('SHOPIFY_APP_HOST_NAME'),
            new FileSessionStorage('/tmp/php_sessions'),
            '2021-04',
            true,
            false,
        );
    }
}

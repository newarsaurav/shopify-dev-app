<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shopify\Auth\OAuth;
use Shopify\Auth\OAuthCookie;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        return view('index');
    }

    public function addShop(Request $request)
    {
        Shop::updateOrCreate([
            'shop_url' => $request->shop_url 
        ], [
            'shop_url' => $request->shop_url 
        ]);

        $redirect_url = OAuth::begin(
            $request->shop_url,
            'https://d9d7-2400-1a00-b020-df64-ac0b-9e65-e52c-f810.ngrok-free.app/auth/callback',
            true
        );

        return redirect($redirect_url);
    }


    public function login(Request $request)
    {
        $cookies = [];
        $redirect_url = OAuth::begin(
            'saurav-test-pros.myshopify.com',
            'https://d9d7-2400-1a00-b020-df64-ac0b-9e65-e52c-f810.ngrok-free.app/auth/callback',
            true
        );
        
        return redirect($redirect_url);
    }
 
    public function generateToken(Request $request)
    {
        $params = $request->all();
        $hmac = $request->hmac;
        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexographically

        $computed_hmac = hash_hmac('sha256', http_build_query($params), $_ENV['SHOPIFY_API_SECRET']);

        // Use hmac data to check that the response is from Shopify or not
        if (hash_equals($hmac, $computed_hmac)) {

            // Set variables for our request
            $query = array(
                "client_id" => $_ENV['SHOPIFY_API_KEY'], // Your API key
                "client_secret" => $_ENV['SHOPIFY_API_SECRET'], // Your app credentials (secret key)
                "code" => $params['code'] // Grab the access key from the URL
            );

            // Generate access token URL
            $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

            // Configure curl client and execute request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $access_token_url);
            curl_setopt($ch, CURLOPT_POST, count($query));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
            $result = curl_exec($ch);
            curl_close($ch);

            // Store the access token
            $result = json_decode($result, true);

            if (isset($result['access_token'])) {
                Shop::updateOrCreate(
                    [
                        'shop_url' => $params['shop'],
                    ], 
                    [
                        'shop_id' => isset($result['associated_user']['id']) ? $result['associated_user']['id'] : NULL,
                        'access_token' => $result['access_token']
                    ]
                );

                $access_token = $result['access_token'];

                //Install Webhooks for sending informations after app uninstall/delete
                $webhook = $this->addWebhooks('delete', $access_token, $params);

                return [$access_token, $webhook];
            }
            return 'Somethiung Went Wrong';
            // Show the access token (don't do this in production!)
        } else {
            // Someone is trying to be shady!
            return ('This request is NOT from Shopify!');
        }
    }

    public function addWebhooks(String $type, String $accesstoken, array $params)
    {
        $url = "https://" . $params['shop'] . "/admin/api/2022-04/webhooks.json";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "X-Shopify-Access-Token: ".$accesstoken,
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = '{"webhook":{"topic":"app/uninstalled","address":"https://d9d7-2400-1a00-b020-df64-ac0b-9e65-e52c-f810.ngrok-free.app/shopify/uninstall/","format":"json","fields":[]}}';

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        return($resp);
    }
}

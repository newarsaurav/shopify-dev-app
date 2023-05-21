<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Mail\AppUninstalledMail;
use App\Models\Shop;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
  public function appUninstall(Request $request)
  {

    Shop::where('shop_id', $request->id)->delete();

    $uninstall_response = $this->uninstallApp();

    $uninstall_response = json_decode($uninstall_response['response']);
    $reason = $uninstall_response->data->app->events->edges[0]->node->reason;
    Log::emergency($reason);


    $client_email = $request->customer_email;
    $shop_owner = $request->shop_owner;

    $send_email = Mail::to($client_email)->send((new AppUninstalledMail($reason, $shop_owner))
      ->subject($reason));
  }

  public function uninstallApp()
  {

    $accesstoken = 'prtapi_3cffdbf94d83b0d44cb7b66873866a86';
    $url = "https://partners.shopify.com/2424693/api/2021-10/graphql.json";

    $query = array(
      "query" => '{
        app(id: "gid://partners/App/6750741") {
          id
          name
          events(
              types: [RELATIONSHIP_UNINSTALLED],
              shopId: "gid://partners/Shop/55594090575,"
          ) {
            edges {
              node {
                ... on RelationshipUninstalled {
                  reason
                  description
                }
              }
            }
          }
        }
      }'
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);


    // Setup headers
    $request_headers[] = "";
    $request_headers[] = "Content-Type: application/json";
    if (!is_null($accesstoken)) $request_headers[] = "X-Shopify-Access-Token: " . $accesstoken;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query));
    curl_setopt($curl, CURLOPT_POST, true);


    // Send request to Shopify and capture any errors
    $response = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_message = curl_error($curl);
    // Close cURL to be nice
    curl_close($curl);

    // Return an error is cURL has a problem
    if ($error_number) {
      return $error_message;
    } else {

      // No error, return Shopify's response by parsing out the body and the headers
      $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

      // Convert headers into an array
      $headers = array();
      $header_data = explode("\n", $response[0]);
      $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
      array_shift($header_data); // Remove status, we've already set it above
      foreach ($header_data as $part) {
        $h = explode(":", $part);
        $headers[trim($h[0])] = trim($h[1]);
      }

      // Return headers and Shopify's response
      return array('headers' => $headers, 'response' => $response[1]);
    }
  }

  public function removeShopData(Request $request)
  {
    Shop::where('shop_id', $request->id)->delete();
    Log::emergency('Shop Data Removed with id: ' . $request->id);
  }
}

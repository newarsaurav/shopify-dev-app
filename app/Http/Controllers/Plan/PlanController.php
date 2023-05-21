<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class PlanController extends Controller
{
    public function index()
    {
        // $plans = shopify_api_call('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', 'saurav-devlopment-store', '/admin/api/2022-04/products.json');
        // $products = (json_decode($res['response']));
        $products = [];
        return view('Plan.index', compact('products'));
    }

    public function purchaseMonthlyPlan(Request $request, $amount)
    {
        $plan = $this->addMonthlySubscription('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', $amount);
        $response = json_decode($plan);
        if (isset($response->recurring_application_charge)) {
            return redirect($response->recurring_application_charge->confirmation_url);
        }
        return view('Plan.index', compact('products'));
    }

    public function addMonthlySubscription(String $accesstoken, $amount)
    {
        $url = "https://saurav-devlopment-store.myshopify.com/admin/api/2022-04/recurring_application_charges.json";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "X-Shopify-Access-Token: " . $accesstoken,
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = '{
            "recurring_application_charge": {
              "name": "Monthly Recurring charge",
              "price": ' . $amount . ',
              "return_url": "https://261b-2400-1a00-b010-b06-5945-8139-f51-d670.ngrok-free.app/plan",
              "test" : true
            }
          }';

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        return ($resp);
    }


    public function purchaseAnnualPlan(Request $request, $amount)
    {
        $plan = $this->addAnnualSubscription('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', $amount);
        $response = json_decode($plan['response']);
        if (isset($response->data->appSubscriptionCreate)) {
            return redirect($response->data->appSubscriptionCreate->confirmationUrl);
        }
        return view('Plan.index', compact('products'));
    }

    public function addAnnualSubscription(String $accesstoken, $amount)
    {
        $url = "https://saurav-devlopment-store.myshopify.com/admin/api/unstable/graphql.json";

        $query = array("query" => '
            mutation {
                appSubscriptionCreate(
                    name: "Annual Subscription Charge"
                    returnUrl: "https://261b-2400-1a00-b010-b06-5945-8139-f51-d670.ngrok-free.app/plan"
                    test: true
                    lineItems: [
                    {
                        plan: {
                            appRecurringPricingDetails: {
                                price: { amount: '.$amount.', currencyCode: USD }
                                interval: ANNUAL
                            }
                        }
                    }
                    ]
                ) {
                    appSubscription {
                        id
                    }
                    confirmationUrl
                    userErrors {
                        field
                        message
                    }
                }
            }'
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);


        // Setup headers
        $request_headers[] = "";
        $request_headers[] = "Content-Type: application/json";
        $request_headers[] = "Accept: application/json";
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
}

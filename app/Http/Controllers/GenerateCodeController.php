<?php

namespace App\Http\Controllers;

use App\Helpers\Functions;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GenerateCodeController extends Controller
{
    use Functions;

    public function __construct()
    {

    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function generateCode(Request $request)
    {

        try {
            //checkValidate
            $this->validateGenerateCode($request);

            //inputs
            $amount = $request->input('amount');
            $customerProfileId = $request->input('customerProfileId');
            $customerMobile = $request->input('customerMobile');
            $description = $request->input('description');
            $itemId = $request->input('itemId');
            $itemDescription = $request->input('itemDescription');
            $price = $request->input('price');
            $quantity = $request->input('quantity');
            
            $amount = sprintf('%0.2f', $amount);

            $pin = mt_rand(1000000, 9999999)
            . mt_rand(1000000, 9999999);

            // shuffle the result
            $randomRefernceCode = str_shuffle($pin);
            $randomRefernceCode = $randomRefernceCode.''.$customerProfileId;
            
           /* do {
                // generate a reference number on 2 * 7 digits + a random character
                $pin = mt_rand(1000000, 9999999)
                . mt_rand(1000000, 9999999);

                // shuffle the result
                $randomRefernceCode = str_shuffle($pin);

                //check if reference isset
                $getChargeRequest = $this->fawryCodeRepo->checkCodeIfIsset($randomRefernceCode);
            } while (!$getChargeRequest);*/

            //create signature
            $shaSignature = hash('sha256', "".env('FAWRY_MERCHANT_CODE')."" . $randomRefernceCode . 555 . "PAYATFAWRY" . $amount . "".env('FAWRY_SECRET_KEY')."");

            $client = new \GuzzleHttp\Client();
            $sendBody = '{"merchantCode":"'.env('FAWRY_MERCHANT_CODE').'","merchantRefNum":"' . $randomRefernceCode . '","customerProfileId":"' . $customerProfileId . '","customerMobile":"' . $customerMobile . '","paymentMethod":"PAYATFAWRY","amount":"' . $amount . '","currencyCode":"EGP","description":"'. $description .'","chargeItems":[ { "itemId":"'.$itemId.'","description":"'.$itemDescription.'","price":"' . $price . '","quantity":'.$quantity.'} ],"signature":"' . $shaSignature . '"}';
            $requestApi = $client->post(''.env('FAWRY_URL').'/ECommerceWeb/Fawry/payments/charge', [
                'body' => $sendBody, 'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ])->getBody()->getContents();

            if (!$requestApi) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            //decode request
            $requestApi = json_decode($requestApi, true);

            return $this->outApiJson('success', 'sucess', "", ['referenceNumber' => $requestApi['referenceNumber'], 'merchantRefNumber' => $requestApi['merchantRefNumber'], 'expirationTime' => $requestApi['expirationTime'], 'signature' => $shaSignature]);
        } catch (\PDOException $e) {
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateGenerateCode($request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'customerProfileId' => 'required',
            'customerMobile' => 'required',
            'description' => 'required',
            'itemId' => 'required',
            'itemDescription' => 'required',
            'price' => 'required',
            'quantity' => 'required'

        ]);
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function checkStatusCode(Request $request)
    {
        try {
            //checkValidate
            $this->validateCheckStatusCode($request);

            //inputs
            $referenceNumber = $request->input('referenceNumber');

            //generate signature
            $shaSignature = hash('sha256', "".env('FAWRY_MERCHANT_CODE')."" . $referenceNumber . "".env('FAWRY_SECRET_KEY')."");

            $client = new \GuzzleHttp\Client();
            $requestApi = $client->get(''.env('FAWRY_URL').'/ECommerceWeb/Fawry/payments/status?merchantCode=1tSa6uxz2nRrtrYnH3xubg==&merchantRefNumber=' . $referenceNumber . '&signature=' . $shaSignature . '')->getBody();

            if (!$requestApi) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            //decode request
            $requestApi = json_decode($requestApi, true);

            return $this->outApiJson('success', 'sucess', "", ['FawryResponse' => $requestApi]);
        } catch (\PDOException $e) {
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateCheckStatusCode($request)
    {
        $this->validate($request, [
            'referenceNumber' => 'required',
        ]);
    }

    /**
     *
     * @param Request $request
     * @param array $errors
     * @return type
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return $this->outApiJson('validation', ['validation_errors'], [$errors]);
    }
}

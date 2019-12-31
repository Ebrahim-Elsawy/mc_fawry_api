<?php

namespace App\Http\Controllers;

use App\Helpers\Functions;
use App\Repositories\FawryCodeRepository;
use App\Repositories\PersonRepository;
use App\Repositories\PromoCodeRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FawryCodeController extends ApiController
{
    protected $personRepo;
    protected $fawryCodeRepo;
    protected $promoCodeRepo;
    protected $jwt;
    use Functions;

    public function __construct(PersonRepository $personRepo, FawryCodeRepository $fawryCodeRepo, PromoCodeRepository $promoCodeRepo)
    {
        $this->personRepo = $personRepo;
        $this->fawryCodeRepo = $fawryCodeRepo;
        $this->promoCodeRepo = $promoCodeRepo;
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function createFawryCode(Request $request)
    {

        try {
            //checkValidate
            $this->validateCreateFawryCode($request);

            //inputs
            $amount = $request->input('amount');
            $amount = sprintf('%0.2f', $amount);
            $userId = $request->input('userId');
            $promoCode = $request->input('promoCode');

            //checkUser
            $user = $this->personRepo->findBy('person_uid', $userId);
            if (!$user) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            //check if promocode
            if ($promoCode) {
                $getCode = $this->promoCodeRepo->getPromo($promoCode, $userId);
                if (!$getCode) {
                    return $this->outApiJson('not-found-data', 'Not found data', "", ["error_in_promo"]);
                }
                $calculateAmount = ($amount * $getCode->percentage) / 100;
                $discountAmount = $amount - $calculateAmount;
                $amount = sprintf('%0.2f', $discountAmount);
            }

            do {
                // generate a pin based on 2 * 7 digits + a random character
                $pin = mt_rand(1000000, 9999999)
                . mt_rand(1000000, 9999999);

                // shuffle the result
                $randomRefernceCode = str_shuffle($pin);

                //check if reference isset
                $getChargeRequest = $this->fawryCodeRepo->checkCodeIfIsset($randomRefernceCode);
            } while (!$getChargeRequest);
            $shaSignature = hash('sha256', "".env('FAWRY_MERCHANT_CODE')."". $randomRefernceCode . $userId . "PAYATFAWRY" . $amount . "".env('FAWRY_SECRET_KEY')."");

            $client = new \GuzzleHttp\Client();
            $sendBody = '{"merchantCode":"'.env('FAWRY_MERCHANT_CODE').'","merchantRefNum":"' . $randomRefernceCode . '","customerProfileId":"' . $userId . '","customerMobile":"' . $user->person_mobile . '","paymentMethod":"PAYATFAWRY","amount":"' . $amount . '","currencyCode":"EGP","description":"the charge request for fawry","chargeItems":[ { "itemId":"888","description":"the fawry code request","price":"' . $amount . '","quantity":1} ],"signature":"' . $shaSignature . '"}';
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

            //insert fawry code
            $data['amount'] = $amount;
            $data['user_id'] = $userId;
            $data['reference_code'] = $requestApi['referenceNumber'];
            $data['reference_number'] = $requestApi['merchantRefNumber'];
            $data['signature'] = $shaSignature;
            $data['expiration_time'] = $requestApi['expirationTime'];
            $data['status'] = 0;

            $addFawryCode = $this->fawryCodeRepo->addFawryCode($data);
            if (!$addFawryCode) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            return $this->outApiJson('success', 'sucess', "", ['referenceNumber' => $requestApi['referenceNumber'], 'merchantRefNumber' => $requestApi['merchantRefNumber'], 'expirationTime' => $requestApi['expirationTime'], 'signature' => $shaSignature]);
        } catch (\PDOException $e) {
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateCreateFawryCode($request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'userId' => 'required',
        ]);
    }

    /**
     * get all patients
     */
    public function getPatientUsers()
    {
        try {
            //get users
            $users = $this->personRepo->getPatientUsers();
            if (!$users) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }
            return $this->outApiJson('success', 'sucess', "", ['users' => $users]);
        } catch (\PDOException $e) {
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function checkPaymentStatusCode(Request $request)
    {
        try {
            //checkValidate
            $this->validateCheckPaymentStatusCode($request);

            //inputs
            $code = $request->input('code');

            //get code
            $getData = $this->fawryCodeRepo->getFawryCode($code);
            if (!$getData) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }
            //check status
            $shaSignature = hash('sha256', "".env('FAWRY_MERCHANT_CODE')."" . $getData->reference_number . "".env('FAWRY_SECRET_KEY')."");

            $client = new \GuzzleHttp\Client();
            $requestApi = $client->get(''.env('FAWRY_URL').'/ECommerceWeb/Fawry/payments/status?merchantCode='.env('FAWRY_MERCHANT_CODE').'&merchantRefNumber=' . $getData->reference_number . '&signature=' . $shaSignature . '')->getBody();
            if (!$requestApi) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            //decode request
            $requestApi = json_decode($requestApi, true);

            if ($requestApi['paymentStatus'] != 'UNPAID') {
                if ($requestApi['paymentStatus'] == 'PAID') {
                    $data['status'] = 1;
                    $updateStatusCode = $this->fawryCodeRepo->updateStatusCode($data, $getData->id);
                } else {
                    $data['status'] = 2;
                    $updateStatusCode = $this->fawryCodeRepo->updateStatusCode($data, $getData->id);
                }
            }

            $getDataAfterUpdated = $this->fawryCodeRepo->getFawryCode($code);
            if (!$getDataAfterUpdated) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            return $this->outApiJson('success', 'sucess', "", ['getCode' => $getDataAfterUpdated, 'FawryResponse' => $requestApi]);
        } catch (\PDOException $e) {
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateCheckPaymentStatusCode($request)
    {
        $this->validate($request, [
            'code' => 'required',
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

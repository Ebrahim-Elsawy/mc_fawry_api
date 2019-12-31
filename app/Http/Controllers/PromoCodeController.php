<?php

namespace App\Http\Controllers;

use App\Helpers\Functions;
use App\Repositories\PromoCodeRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PromoCodeController extends ApiController
{
    protected $promoCodeRepo;
    protected $jwt;
    use Functions;

    public function __construct(PromoCodeRepository $promoCodeRepo)
    {
        $this->promoCodeRepo = $promoCodeRepo;
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function createPromoCode(Request $request)
    {

        try {
            //checkValidate
            $this->validateCreatePromoCode($request);

            //inputs
            $promoCode = $request->input('code');
            $percentage = $request->input('percentage');
            $userId = $request->input('userId');

            //insert promo code
            $data['code'] = $promoCode;
            $data['percentage'] = $percentage;
            $data['user_id'] = $userId;
            $data['status'] = 1;

            $addPromoCode = $this->promoCodeRepo->addPromoCode($data);
            if (!$addPromoCode) {
                return $this->outApiJson('not-found-data', 'Not found data', "", ["not_found_data"]);
            }

            return $this->outApiJson('success', 'sucess', "", ['promoCode' => $addPromoCode]);
        } catch (\PDOException $e) {
            dd($e->getMessage());
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateCreatePromoCode($request)
    {
        $this->validate($request, [
            'code' => 'required|unique:promo_codes|min:4',
            'percentage' => 'required',
            'userId' => 'required',
        ]);
    }

        /**
     *
     * @param Request $request
     * @return type
     */
    public function changeStatusPromoCode(Request $request)
    {

        try {
            //checkValidate
            $this->validateChangeStatusPromoCode($request);

            //inputs
            $promoCode = $request->input('code');


            $changeStatusCode = $this->promoCodeRepo->changeStatusCode($promoCode);
            if (!$changeStatusCode) {
                return $this->outApiJson('not-found-data', 'Not found code', "", ["not_found_data"]);
            }

            return $this->outApiJson('success', 'sucess', "", ['promoCode' => $changeStatusCode]);
        } catch (\PDOException $e) {
            dd($e->getMessage());
            return $this->outApiJson('exception', ['exception']);
        }
    }

    /**
     *
     * @param type $request
     */
    public function validateChangeStatusPromoCode($request)
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

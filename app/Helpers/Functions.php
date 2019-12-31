<?php

namespace App\Helpers;

use Aws\Ses\Exception\SesException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Support\Facades\Mail;

/**
 * Trait Functions
 * @package App\Helpers
 *
 */
trait Functions
{

    /**
     * @param bool $status
     * @return array|mixed
     */
    public function statusCodes($status = false)
    {
        $array = [
            'success' => 200,
            'validation' => 201,
            'not-found-data' => 202,
            'error-update' => 203,
            'error-insert' => 204,
            'exception' => 205,
            'error-upload' => 206,
            'error-old-password' => 207,
            'row-isset' => 208,
            'error-delete' => 209,
            'token-invalid' => -1,
            'token-absent' => -2,
            'token-expired' => -3,
            'user-not-found' => -4,
            'JWT_Exception' => -5,
            'Invalid_Claim_Exception' => -6,
            'Payload_Exception' => -7,
            'Blacklisted_token' => -8
        ];
        if ($status) {
            return $array[$status];
        }
        return $array;
    }

    /**
     * @param $statusCode
     * @param array $messages
     * @param array $data
     * @param int $responseStatus
     * @return \Illuminate\Http\JsonResponse
     */
    public function outApiJson($statusCode,$messages, $errors = "", array $data = [], $responseStatus = 200)
    {
        if($errors){
        foreach($errors as $error){
            $errors  = $error;
            break;
        }
    }
        $outData = [];
        $outData['code'] = $this->statusCodes($statusCode);
        $outData['messages'] = $messages;
        if($errors){
            $outData['errors'] = "";
            foreach($errors as $error){
                $outData['errors'] = $error;
                break;
            }
            //$outData['errors'] = $errors;
        }else{
            $outData['errors'] = "";
        }
        $outData['data'] = $data;
        return response()->json($outData, $responseStatus);
    }
}

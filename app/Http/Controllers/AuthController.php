<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\JWTAuth;
use App\Helpers\Functions;

class AuthController extends Controller
{

    protected $repo;
    protected $jwt;

    use Functions;

    /**
     * AuthController constructor.
     * @param JWTAuth $jwt
     */
    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        try {
            //check validate 
            $this->validateLogin($request);

            // verify the credentials and create a token for the user
            if ((!$token = $this->jwt->attempt(['person_mobile' => $request->input('mobile'), 'person_password' => $request->input('password'), 'is_admin' => 1, 'person_status' => 1]))) {
                return $this->outApiJson('user-not-found', 'user_not_found');
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->outApiJson('token-expired', 'token_expired');
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->outApiJson('token-invalid', 'token_invalid');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->outApiJson('token-absent', 'token_absent');
        }
        return $this->outApiJson('success', 'success', "", ['token' => $token]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function refreshToken(Request $request)
    {
        try {
            if (!$token = $this->jwt->setRequest($request)->parseToken()->refresh()) {
                return $this->outApiJson('user-not-found', 'user_not_found');
            }
        } catch (TokenExpiredException $e) {
            return $this->outApiJson('token-expired', 'token_expired');
        } catch (TokenInvalidException $e) {
            return $this->outApiJson('token-absent', 'token_absent');
        } catch (JWTException $e) {
            return $this->outApiJson('JWT_Exception', 'JWT_Exception');
        } catch (InvalidClaimException $e) {
            return $this->outApiJson('Invalid_Claim_Exception', 'Invalid_Claim_Exception');
        } catch (PayloadException $e) {
            return $this->outApiJson('Payload_Exception', 'Payload_Exception');
        } catch (TokenBlacklistedException $e) {
            return $this->outApiJson('Blacklisted_token', 'Blacklisted_token');
        }
        if (!$user = $this->jwt->setToken($token)->authenticate()) {
            return $this->outApiJson('user-not-found', 'user_not_found');
        }
        return $this->outApiJson('success', 'success', ['token' => $token]);
        // the token is valid and we have found the user via the sub claim
    }

    /**
     * 
     * @param type $request
     */
    public function validateLogin($request)
    {
        $this->validate($request, [
            'mobile' => 'required',
            'password' => 'required',
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
        return $this->outApiJson('validation', 'validation_errors', $errors);
    }
}

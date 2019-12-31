<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException as TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException as TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException as TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException as JWTException;
use Tymon\JWTAuth\Exceptions\InvalidClaimException as InvalidClaimException;
use Tymon\JWTAuth\Exceptions\PayloadException as PayloadException;
use App\Helpers\Functions;

class JwtMiddleware {

    use Functions;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        try {
            if (!$user = JWTAuth::setRequest($request)->parseToken()->authenticate()) {
                return $this->outApiJson('user-not-found', ['user_not_found']);
            }
        } catch (TokenExpiredException $e) {
            return $this->outApiJson('token-expired', ['token_expired']);
        } catch (TokenInvalidException $e) {
            return $this->outApiJson('token-invalid', ['token_invalid']);
        } catch (JWTException $e) {
            return $this->outApiJson('JWT_Exception', ['JWT_Exception']);
        } catch (InvalidClaimException $e) {
            return $this->outApiJson('Invalid_Claim_Exception', ['Invalid_Claim_Exception']);
        } catch (PayloadException $e) {
            return $this->outApiJson('Payload_Exception', ['Payload_Exception']);
        } catch (TokenBlacklistedException $e) {
            return $this->outApiJson('Blacklisted_token', ['Blacklisted_token']);
        }
        $request->user = $user;
        return $next($request);
    }

}

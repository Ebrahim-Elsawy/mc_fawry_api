<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

$router->post('/', 'AuthController@auth');
$router->post('/refresh', 'AuthController@refreshToken');

// charge wallet for mobile
$router->post('/charge-wallet', 'ChargeRequestController@chargeWallet');
$router->post('/check-payment-status-codes', 'ChargeRequestController@checkPaymentStatusCodes');

//generate 
$router->post('/generate-code', 'GenerateCodeController@generateCode');
$router->post('/check-status-code', 'GenerateCodeController@checkStatusCode');

//get user login
$router->group(['middleware' => 'jwt-auth'], function () use ($router) {

  //fawry code
  $router->post('/create-fawry-code', 'FawryCodeController@createFawryCode');
  $router->post('/patient-users', 'FawryCodeController@getPatientUsers');
  $router->post('/check-payment-status-code', 'FawryCodeController@checkPaymentStatusCode');

  //promo code
  $router->post('/create-promo-code', 'PromoCodeController@createPromoCode');
  $router->post('/change-status-code', 'PromoCodeController@changeStatusPromoCode');
});


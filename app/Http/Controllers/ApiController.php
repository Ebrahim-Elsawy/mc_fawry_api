<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{

    public $user;
    public $request;
    public function __construct(Request $request)
    {
        if (isset($request->user)) {
            $this->user = $request->user;
            unset($request->user);
        }
    }

}

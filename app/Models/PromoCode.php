<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $connection = 'mysql';
    protected $table = 'promo_codes';

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FawryCode extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $connection = 'mysql';
    protected $table = 'invoices';
}

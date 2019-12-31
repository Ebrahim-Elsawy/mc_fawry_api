<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    public $timestamps = false;
    protected $guarded = ['person_uid'];
    protected $connection = 'mysql';
    protected $table = 'u_persons';

}

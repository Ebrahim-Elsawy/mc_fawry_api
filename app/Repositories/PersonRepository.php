<?php

namespace App\Repositories;

use App\Helpers\Functions;
use Bosnadev\Repositories\Eloquent\Repository;

class PersonRepository extends Repository
{

    use Functions;

    public function model()
    {
        return '\App\Models\Person';
    }

    public function updateUserWallet($amount, $id)
    {
        $user = $this->model->where('person_uid',$id)->first();
        $wallet = $user->wallet + $amount;
        $response = $this->model->where('person_uid',$id)->update(['wallet' => $wallet]);
        if ($response) {
            return true;
        } else {
            return false;
        }
    }

    public function getPatientUsers(){
        $getUsers = $this->model->where('is_patient',1)->get();
        if(!$getUsers){
            return false;
        }else{
            return $getUsers;
        }
    }
}

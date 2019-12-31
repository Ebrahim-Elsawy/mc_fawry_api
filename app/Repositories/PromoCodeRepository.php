<?php

namespace App\Repositories;

use App\Helpers\Functions;
use Bosnadev\Repositories\Eloquent\Repository;

class PromoCodeRepository extends Repository
{

    use Functions;

    public function model()
    {
        return '\App\Models\PromoCode';
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function addPromoCode($data)
    {
        $response = $this->create($data);
        if ($response) {
            return $response;
        } else {
            return $false;
        }
    }

    /**
     * @param type $code
     * @return type
     */
    public function changeStatusCode($code){
        $getCode = $this->model->where('code',$code)->first();
        if(!$getCode){
            return false;
        }else{
            if($getCode->status == 0){
                $changeStatus = $this->model->where('code',$code)->update(['status' => 1]);
            }else{
                $changeStatus = $this->model->where('code',$code)->update(['status' => 0]);
            }
            $getUpdatedCode = $this->model->where('code',$code)->first();
            return $getUpdatedCode;
        }
    }

    /**
     * @param type $promo
     * @param type $user
     * @return type
     */
    public function getPromo($promo, $user)
    {
        $getPromo = $this->model->where([['code', $promo], ['user_id', $user], ['status', 1]])->orderBy('id', 'DESC')->first();
        if (!$getPromo) {
            return false;
        } else {
            return $getPromo;
        }
    }
}

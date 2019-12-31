<?php

namespace App\Repositories;

use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Criteria\AdvancedSearchCriteria;
use App\Helpers\Functions;

class FawryCodeRepository extends Repository
{

    use Functions;

    public function model()
    {
        return '\App\Models\FawryCode';
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    public function addFawryCode($data)
    {
        $response = $this->create($data);
        if ($response) {
            return $this->outApiJson('success', 'sucess', "", ['branch' => $response]);
        } else {
            return $this->outApiJson('error-insert', ['faild_insert']);
        }
    }

    public function checkCodeIfIsset($referenceNumber)
    {
        $checkCode = $this->model->where('reference_number',$referenceNumber)->count();
        if($checkCode > 0){
            return false;
        }else{
            return true;
        }
    }

    public function getFawryCode($code){
        $checkCode = $this->model->where('reference_code',$code)->first();
        if(!$checkCode){
            return false;
        }else{
            return $checkCode;
        }
    }

    public function getUnpaidCodes(){
        $getCodes = $this->model->where('status',0)->get();
        if(!$getCodes){
            return false;
        }else{
            return $getCodes;
        }
    }

    public function updateStatusCode($data,$id){
        $response = $this->update($data, $id);
        if($response){
            return true;
        }else{
            return false;
        }
    }
}

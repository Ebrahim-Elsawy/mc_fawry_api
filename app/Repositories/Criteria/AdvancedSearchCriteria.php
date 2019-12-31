<?php
namespace App\Repositories\Criteria;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;

class AdvancedSearchCriteria extends Criteria
{
    protected static $where_object = null;

    public function apply($model, Repository $repository)
    {
        $where_obj = self::$where_object;
        if ($where_obj !== null) {
            $model = $this->makeWhere($model, $where_obj);
        }
        return $model;
    }

    public static function setWhereObject(WhereObject $where_object)
    {
        self::$where_object = $where_object;
    }

    /**
     * @param $model
     * @param WhereObject $where
     * @return mixed
     */
    private function makeWhere($model, WhereObject $where)
    {
        $ands = $where->getAnds();
        $ors = $where->getOrs();
        $orders = $where->getOrders();
        $has = $where->getHas();
        $has_ands = $has['and'];
        $has_ors = $has['or'];
        if(count($ands)>0) {
            foreach ($ands as $and) {
                switch ($and['where_type']) {
                    case "where_in":
                        $model = $model->whereIn($and['field'], $and['value']);
                        break;
                    case "where_between":
                        $model = $model->whereBetween($and['field'], $and['value']);
                        break;
                    case "where_not_between":
                        $model = $model->whereNotBetween($and['field'], $and['value']);
                        break;
                    case "where_not_in":
                        $model = $model->whereNotIn($and['field'], $and['value']);
                        break;
                    case "where_null":
                        $model = $model->whereNull($and['field']);
                        break;
                    case "where_not_null":
                        $model = $model->WhereNotNull($and['field']);
                        break;

                    default:
                        $model = $model->Where($and['field'], $and['relation'], $and['value']);
                }
            }
        }
        if (count($ors) > 0) {
            $model = $model->orWhere(function ($query) use ($ors) {
                foreach ($ors as $or) {
                    switch ($or['where_type']) {
                        case "where_in":
                            $query = $query->whereIn($or['field'], $or['value']);
                            break;
                        case
                            "where_between":
                            $query = $query->whereBetween($or['field'], $or['value']);
                            break;
                        case "where_not_between":
                            $query = $query->whereNotBetween($or['field'], $or['value']);
                            break;
                        case "where_not_in":
                            $query = $query->whereNotIn($or['field'], $or['value']);
                            break;
                        case "where_null":
                            $query = $query->whereNull($or['field']);
                            break;
                        case "where_not_null":
                            $query = $query->WhereNotNull($or['field']);
                            break;

                        default:
                            $query = $query->Where($or['field'], $or['relation'], $or['value']);
                    }
                }
            });
        }

        if(count($orders)>0){
            foreach($orders as $order){
                $model = $model->orderBy($order['field'],$order['order']);
            }
        }
        if(count($has_ands)){
            foreach($has_ands as $has_and){
                $model = $model->whereHas($has_and['func'],function($q) use($has_and,$model){
                    $q =  $this->makeWhere($q,$has_and['obj']);
                });
            }
        }

        if(count($has_ors)){
            foreach($has_ors as $func=>$has_or){
                $model = $model->orWhereHas($has_or['func'],function($q) use($has_or,$model){
                    $q =  $this->makeWhere($q,$has_or['obj']);
                });
            }
        }

        return $model;
    }
}
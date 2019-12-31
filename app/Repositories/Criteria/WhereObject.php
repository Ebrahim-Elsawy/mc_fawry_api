<?php

namespace App\Repositories\Criteria;


class WhereObject
{
    private $ors = [];
    private $ands = [];
    private $orders = [];
    private $has = [];

    /**
     * WhereObject constructor.
     */
    public function __construct()
    {
        $this->has['and'] =[];
        $this->has['or'] =[];
    }

    /**
     * @param $sub_function
     * @param WhereObject $where_obj
     */
    public function pushHas($sub_function, WhereObject $where_obj)
    {
        if(count($where_obj->getAnds())>0 || count($where_obj->getOrs())>0 ||
            count($where_obj->getHas()['and'])>0 || count($where_obj->getHas()['or'])>0
            ||count($where_obj->getOrders())>0  ) {
            $this->has['and'][] = ['func' => $sub_function, 'obj' => $where_obj];
        }
    }

    /**
     * @param $sub_function
     * @param WhereObject $where_obj
     */
    public function pushOrHas($sub_function, WhereObject $where_obj)
    {
        if(count($where_obj->getAnds())>0 || count($where_obj->getOrs())>0 ||
            count($where_obj->getHas()['and'])>0 || count($where_obj->getHas()['or'])>0
            ||count($where_obj->getOrders())>0  ) {
            $this->has['or'][] = ['func' => $sub_function, 'obj' => $where_obj];
        }
    }

    /**
     * @param $field
     * @param $order desc | asc
     */
    public function pushOrder($field, $order)
    {
        $this->orders[] = ['field' => $field, 'order' => $order];
    }

    /**
     * @param $field the field name
     * @param $value the value of the field
     * @param string $relation relation  eq|neq|gt|gte|lt|lte|contain|start_with|end_with
     */
    public function pushWhere($field, $value, $relation = 'eq')
    {
        if(!empty($value) || $value ==0 ) {
            $this->ands[] = $this->handelRelations('where', $field, $value, $relation);
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushWhereIn($field, array $value)
    {
        if(!empty($value)) {
            $this->ands[] = ['where_type' => 'where_in', 'field' => $field, 'value' => $value];
        }
    }


    /**
     * @param $field
     * @param array $value
     */
    public function pushWhereBetween($field, array $value)
    {
        if(!empty($value)) {
            $this->ands[] = ['where_type' => 'where_between', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushWhereNotBetween($field, array $value)
    {
        if(!empty($value)) {
            $this->ands[] = ['where_type' => 'where_not_between', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushWhereNotIn($field, array $value)
    {
        if(!empty($value)) {
            $this->ands[] = ['where_type' => 'where_not_in', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     */
    public function pushWhereNull($field)
    {
        $this->ands[] = ['where_type' => 'where_null', 'field' => $field];
    }

    /**
     * @param $field
     */
    public function pushWhereNotNull($field)
    {
        $this->ands[] = ['where_type' => 'where_not_null', 'field' => $field];
    }


    /**
     * @param $field the field name
     * @param $value the value of the field
     * @param string $relation relation  eq|neq|gt|gte|lt|lte|contain|start_with|end_with
     */
    public function pushOrWhere($field, $value, $relation = 'eq')
    {
        if(!empty($value) || $value ==0 ) {
            $this->ors[] = $this->handelRelations('where', $field, $value, $relation);
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushOrWhereIn($field, array $value)
    {
        if(!empty($value)) {
            $this->ors[] = ['where_type' => 'where_in', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushOrWhereNotIn($field, array $value)
    {
        if(!empty($value)) {
            $this->ors[] = ['where_type' => 'where_not_in', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     */
    public function pushOrWhereNull($field)
    {
        $this->ors[] = ['where_type' => 'where_null', 'field' => $field];
    }

    /**
     * @param $field
     */
    public function pushOrWhereNotNull($field)
    {
        $this->ors[] = ['where_type' => 'where_not_null', 'field' => $field];
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushOrWhereBetween($field, array $value)
    {
        if(!empty($value)) {
            $this->ors[] = ['where_type' => 'where_between', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @param $field
     * @param array $value
     */
    public function pushOrWhereNotBetween($field, array $value)
    {
        if(!empty($value)) {
            $this->ors[] = ['where_type' => 'where_not_between', 'field' => $field, 'value' => $value];
        }
    }

    /**
     * @return array
     */
    public function getOrs()
    {
        return $this->ors;
    }

    /**
     * @return array
     */
    public function getAnds()
    {
        return $this->ands;
    }

    /**
     * @return array
     */
    public function getHas()
    {
        return $this->has;
    }

    /**
     * @return array
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param $where_type
     * @param $field
     * @param $value
     * @param $relation
     * @return array
     */
    private function handelRelations($where_type, $field, $value, $relation)
    {
        switch ($relation) {
            case "eq":
                $rel = '=';
                break;
            case "neq":
                $rel = '<>';
                break;
            case "gt":
                $rel = '>';
                break;
            case "gte":
                $rel = '>=';
                break;
            case "lt":
                $rel = '<';
                break;
            case "lte":
                $rel = '<=';
                break;

            case "contain":
                $rel = 'like';
                $value = "%{$value}%";
                break;

            case "start_with":
                $rel = 'like';
                $value = "{$value}%";
                break;
            case "end_with":
                $rel = 'like';
                $value = "%{$value}";
                break;
            default:
                $rel = '=';
        }

        return ['where_type' => $where_type, 'field' => $field, 'value' => $value, 'relation' => $rel];
    }


}
<?php

namespace App\Models;

use CodeIgniter\Model;

class MealCategoryModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'meal_categories';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['client_id', 'category_name', 'category_status', 'updated_at'];


    public  $status  = array('ACTIVE' => '1', 'PENDING' => '2', 'INACTIVE' => '3', 'DELETE' => '0');


    public function updateRecord($data, $where)
    {
        return $this->db->table($this->table)
            ->where($where)
            ->update($data);
    }

    public function insertRecord($data)
    {

        $this->db->table($this->table)->insert($data);
        return $this->db->insertID();
    }

    public function listAll($clientId = '',$status ='')
    {
        $dbData = $this->db->table($this->table)->select('meal_categories.*');
        if (!empty($clientId)) {
            $dbData->where('client_id', $clientId);
            # $dbData->join('camps','camps.id = meal_categories.camp_id');
            #$dbData->where('camps.client_id', $clientId);
        }
        if ($status != '') {
            $dbData->where('category_status', $status);
        } else {
            $dbData->where('category_status !=', $this->status['DELETE']);
        }
        #$dbData->where('category_status !=', $this->status['DELETE']);
        $responseData = $dbData->orderBy('meal_categories.id', 'DESC')->get()->getResult();
        return $responseData;
    }

    public function getById($id, $clientId = '')
    {
        $dbData = $this->db->table($this->table)->select('meal_categories.*');
        if (!empty($clientId)) {
            $dbData->where('client_id', $clientId);
            #$dbData->join('camps','camps.id = meal_categories.camp_id');
            #$dbData->where('camps.client_id', $clientId);
        }
        $dbData->where('category_status !=', $this->status['DELETE']);
        $responseData = $dbData->where('meal_categories.id', $id)->get()->getRow();
        return $responseData;
    }

    public function getCategories($categoryIds = '')
    {
        $dbData = $this->db->table($this->table)->select('meal_categories.*');
        if (!empty($categoryIds)) {
            $dbData->whereIn('id', $categoryIds);
        }
        $dbData->where('category_status !=', $this->status['DELETE']);
        $responseData = $dbData->orderBy('meal_categories.id', 'DESC')->get()->getResult();
        return $responseData;
    }
}

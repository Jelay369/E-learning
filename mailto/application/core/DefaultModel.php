<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class DefaultModel extends CI_Model{
    
    protected $db;
    protected $table;
    
    public function __construct()
    {
        $this->db = new Database();
    }


    abstract public function update(array $fields,array $values,string $selector = null, $selector_value = null);
    abstract public function delete($id);
    
    public function getOne(string $selector,$value)
    {
        $result = $this->db->select($this->table)
                ->where($selector,"=")
                ->limit(1)
                ->execute([$value]);
        if(empty($result))
            return NULL;
        return $result[0];
    }

    public function create(array $fields,array $values)
    {
        $this->db->insert($this->table)
                ->parametters($fields)
                ->execute($values);
    }
    public function getAll()
    {
       return $this->db->customSelect("SELECT *  FROM ". $this->table);
    }

    public function deleteMultiple(string $field,array $values)
    {
        $query = 'DELETE FROM '.$this->table. ' WHERE ' . $field . ' = ';
        foreach($values as $key=>$value)
        {
            if($key < count($values) - 1)
            {
                $query .= $value . ' OR ' . $field . '=';
            }
            else
            {
                $query .= $value;
            }
        }
       $this->db->customSelect($query);
    }
    public function set_table_name($table_name)
    {
        $this->table = $table_name;
    }
}
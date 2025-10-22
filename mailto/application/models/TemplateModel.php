<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TemplateModel extends DefaultModel{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @override
	 * */
	public function update(array $fields,array $values,string $selector = null, $selector_value = null)
	{
		$values[] = $selector_value;
		$this->db->update($this->table)
				->parametters($fields)
				->where($selector,"=")
				->execute($values);
	}

	/**
	 * @override
	 * */
    public function delete($id)
    {
    	$this->db->delete($this->table)
    			->where("id_template","=")
    			->execute([$id]);
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContactModel extends DefaultModel{
	public function __construct()
	{
		parent::__construct();
	}

	public function allDesc()
	{
		return $this->db->customSelect("SELECT *  FROM ". $this->table ." ORDER BY id_contact DESC");
	}
	public function getAllEmail()
	{
		$emails_tab = [];
		$emails =  $this->db->selectParam($this->table,['email_contact'])
							->execute();
		foreach($emails as $email)
		{
			$emails_tab[] = $email->email_contact;
		}
		return $emails_tab;
	}
	public function createByCSV(string $fields,string $values)
	{
		$this->db::db()->query("INSERT INTO " . $this->table . $fields . " VALUES " . $values);
	}
	public function search($query)
	{
		$query__ = $this->db::db()->prepare("SELECT * FROM ".$this->table." WHERE secteur_activite LIKE :q");
        $query__->execute(["q"=> "%".$query."%"]);
        return $query__->fetchAll(PDO::FETCH_OBJ);
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
    			->where("id_contact","=")
    			->execute([$id]);
    }
}
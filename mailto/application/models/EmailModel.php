<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmailModel extends DefaultModel{
	public function __construct()
	{
		parent::__construct();
	}
	public function allDesc()
	{
		return $this->db->customSelect("SELECT * FROM ". $this->table ." ORDER BY id_mail_envoye DESC");
	}
	public function search($query)
	{
		$query__ = $this->db::db()->prepare("SELECT * FROM ".$this->table." LEFT JOIN contact ON ".$this->table.".destinataire_mail=contact.email_contact LEFT JOIN campagne ON ".$this->table.".campagne_mail=campagne.id_campagne WHERE contact.secteur_activite LIKE :q OR campagne.name_campagne LIKE :q");
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
    			->where("id_mail_envoye","=")
    			->execute([$id]);
    }
}
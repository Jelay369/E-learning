<?php
class FormValidation{
	
	protected $errors = [];

	public function required( string $field, ?string $message = null )
	{
		if( $message === null )
		{
			$message = 'Ce champ est réquis';
		}
		if( empty( trim($_POST[$field]) ) )
		{
			$this->setError($field,$message);
		}
		return $this;
	}
	public function requiredAll( array $posts, ?string $message = null )
	{
		if($message === null)
		{
			$message = "Tous les champs sont requis";
		}
		$valid = true;
		foreach( $posts as $key => $post )
		{
			if( is_array($post) )
			{
				foreach ($post as $value) {
					if( empty( trim( $value ) ) )
					{
						$valid = false;
					}
				}
			}
			else
			{
				if( empty( trim( $post ) ) )
				{
					$valid = false;
				}
			}
		}
		if(!$valid)
		{
			$this->setError("requiredAll",$message);
		}
		return $this;
	}
	public function email(string $email,?string $message = null)
	{
		if($message === null)
		{
			$message = "Adresse email invalide";
		}
		$email = htmlspecialchars($email);
		if(!preg_match("#^[a-z][a-z0-9._-]{1,}@[a-z0-9._-]{2,}\.[a-z]{2,4}$#",$email))
		{
			$this->setError("email", $message);
		}
		return $this;
	}
	public function passwordMin(string $field,int $length,?string $message = null)
	{
		if($message === null)
		{
			$message = 'Le mot de passe doit comporter au moin '.$length.' caractères';
		}
		if( strlen($field) < $length )
		{
			$this->setError('password',$message);
		}
		return $this;
	}
	public function uniq(string $table,string $field,?string $message = null)
	{
		$db = new Database();
		if($message === null)
		{
			$message = 'Valeur existe déjà';
		}
		$res = $db->select($table)
		->where($field,"=")
		->execute([trim($_POST[$field])]);
		if(!empty($res)){
			$this->setError('uniq', $message); 
		}
	}
	public function uniq2(string $table,array $fields,array $values,?string $message = null)
	{
		$db = new Database();
		if($message === null)
		{
			$message = 'Valeur existe dejà';
		}
		$res = $db->select($table)
		->where($fields[0],"=")
		->and($fields[1],"=")
		->execute($values);
		if(!empty($res)){
			$this->setError('uniq', $message); 
		}
		return $this;
	}

	public function setError(string $key, string $message)
	{
		$this->errors[$key] = $message;
	}
	public function getErrors() : array
	{
		return $this->errors;
	}
	public function run() : bool
	{
		if(empty($this->errors))
		{
			return true;
		}
		return false;
	}
}
<?php
class FileController{

	protected $files;
	protected $directory;
	protected $defaultDirectory;
	protected $filename;
	protected $errors = [];

	public function __construct(array $files,?string $directory=null)
	{
		$this->files = $files;
		$this->defaultDirectory =  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Publics' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
		$this->directory = $directory;
		
	}

	public function upload($name =null)
	{
		if($name){
			$this->filename = $name;
		}else{
			$this->filename = uniqid() . "." . $this->getExtension();
		}
		
		if($this->directory)
		{
			$this->defaultDirectory = $this->createDirectory($this->directory);
		}
		foreach($this->files as $file)
		{
			move_uploaded_file($file['tmp_name'],$this->defaultDirectory . $this->filename);
		}
	}
	public function uploadWithName(array $file,string $name)
	{
		$extension = pathinfo($file['name'],PATHINFO_EXTENSION);
		$this->filename = $name . '.' . $extension;
		if($this->directory)
		{
			$this->defaultDirectory = $this->createDirectory($this->directory);
		}
		foreach($this->files as $file)
		{
			move_uploaded_file($file['tmp_name'],$this->defaultDirectory .$this->filename);
		}
	}

	public  function verifyExtension(array $extensions)
	{
		$isValid = true;
		foreach($this->files as $file)
		{
			$filename = $file['name'];
			$extension = pathinfo($filename,PATHINFO_EXTENSION);
			if(!empty($filename) && !in_array(strtolower($extension),$extensions))
			{
				$isValid = false;
			}
		}
		if(!$isValid)
		{
			$this->setErrors('fileExtension', 'Format non supporté');
		}
		return $this;
	}
	public  function required(?string $message=null)
	{
		if($message === null)
		{
			$message = 'Ce champ est requis';
		}
		foreach( $this->files as $file )
		{
			if(empty($file['name']))
			{
				$this->setErrors('fileRequired' , $message); 
			}
		}

		return $this;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function setErrors($key,$message)
	{
		$this->errors[$key] = $message;
	}
	public function getErrors() : array
	{
		return $this->errors;
	}
	public function valideFile():bool
	{
		if(count($this->errors) > 0)
		{
			return false;
		}
		return true;
	}
	public static function extension(array $files,array $extensions) : bool
	{
		$isValid = true;
		foreach($files as $file)
		{
			$filename = $file['name'];
			$extension = pathinfo($filename,PATHINFO_EXTENSION);
			if(!empty($filename) && !in_array(strtolower($extension),$extensions))
			{
				$isValid = false;
			}
		}
		return $isValid;
	}

	private function getExtension() : string
	{
		$extension = "";
		foreach($this->files as $file)
		{
			$filename = $file['name'];
			$extension = pathinfo($filename,PATHINFO_EXTENSION);
		}
		return strtolower($extension);
	}

	private function createDirectory(string $path): string
	{
		$parts = explode('/',$path);
		$formatedPath = '';
		foreach($parts as $part)
		{
			$dir = $formatedPath . $part;
			if(!file_exists( $this->defaultDirectory . $dir) )
			{
				mkdir($this->defaultDirectory . $dir);
			}
			$formatedPath = $dir . DIRECTORY_SEPARATOR;
		}
		return $this->defaultDirectory.$formatedPath;
	}

}

// mp3, ogg
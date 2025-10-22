<?php
class FileManager
{
    public static function verif_extension($file, array $allowed_extensions)
    {
        foreach ($allowed_extensions as $key => $ext) {
            $ext = strtolower($ext);
        }
        $extension = self::get_extension($file);
        if (!in_array($extension, $allowed_extensions))
            return false;

        return true;
    }
    public static function is_null($file)
    {
        if ($file['name'] === "" && $file["size"] === 0)
            return true;
        return false;
    }
    public static function get_extension($file)
    {
        return strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    }
    public static function remove_file($filename)
    {
		if (!is_dir($filename) && file_exists($filename)) {
			return unlink($filename);
		}
    }
    public static function move_file($source, $dest)
    {
        if (copy($source, $dest)) {
            unlink($source);
        }
    }
    public static function upload($source, $destination)
    {
        if (!move_uploaded_file($source, $destination)) {
            throw new Exception();
        }
    }
}

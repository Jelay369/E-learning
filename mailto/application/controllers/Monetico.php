<?php

class Monetico extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("HmacComputerModel","hmac");
    }
    
    
    public function index()
    {
        $data = $_POST;

        if (array_key_exists("MAC", $data)) {
            $receivedSeal = $data['MAC'];
            unset($data['MAC']); // removes the MAC field itself

            $isSealValidated = $this->hmac->validateSeal($data, "d5c3d99bf7ff337de4df59bcc5ab271f791fbf9d", $receivedSeal);
            if ($isSealValidated) {
                file_put_contents(__DIR__.DIRECTORY_SEPARATOR."data", "MAC Valide");
            } else {
                file_put_contents(__DIR__.DIRECTORY_SEPARATOR."data", "MAC invalide");
            }
        } else {
            throw new \InvalidArgumentException("Unable to verify the sealing since received data did not contain MAC field.");
        }
        // file_put_contents(__DIR__.DIRECTORY_SEPARATOR."data", json_encode($_POST));
    }
}
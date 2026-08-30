<?php

namespace models;

class Utilisateur extends \app\Model
{
    public function __construct()
    {
        $this->table = "utilisateur";

        $this->getConnection();
    }
}
?>
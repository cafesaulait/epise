<?php

namespace models;

class Categorie extends \app\Model
{
    public function __construct()
    {
        $this->table = "categorie";

        $this->getConnection();
    }
}
?>
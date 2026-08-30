<?php

namespace models;

class Produit extends \app\Model
{
    public function __construct()
    {
        $this->table = "produit";

        $this->getConnection();
    }
}
?>
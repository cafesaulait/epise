<?php

namespace models;

class Panier extends \app\Model
{
    public function __construct()
    {
        $this->table = "panier";

        $this->getConnection();
    }
}
?>
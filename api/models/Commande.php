<?php

namespace models;

class Commande extends \app\Model
{
    public function __construct()
    {
        $this->table = "commande";

        $this->getConnection();
    }
}
?>
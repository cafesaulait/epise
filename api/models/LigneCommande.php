<?php

namespace models;

class LigneCommande extends \app\Model
{
    public function __construct()
    {
        $this->table = "ligne_commande";

        $this->getConnection();
    }
}
?>
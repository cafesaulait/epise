<?php

namespace models;

class PanierProduit extends \app\Model
{
    public function __construct()
    {
        $this->table = "panier_produit";

        $this->getConnection();
    }
}
?>
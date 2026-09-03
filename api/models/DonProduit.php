<?php

namespace models;

class DonProduit extends \app\Model
{
    public function __construct()
    {
        $this->table = "don_produit";
        $this->primaryKey = "id_don_produit";

        $this->getConnection();
    }
}

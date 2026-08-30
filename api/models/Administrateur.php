<?php

namespace models;

class Administrateur extends \app\Model
{
    public function __construct()
    {
        $this->table = "administrateur";

        $this->getConnection();
    }
}
?>
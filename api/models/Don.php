<?php

namespace models;

class Don extends \app\Model
{
    public function __construct()
    {
        $this->table = "don";

        $this->getConnection();
    }
}
?>
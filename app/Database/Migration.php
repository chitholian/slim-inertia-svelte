<?php

namespace App\Database;

abstract class Migration
{
    protected DB $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public abstract function up();

    public abstract function down();
}

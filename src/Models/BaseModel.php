<?php

namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}
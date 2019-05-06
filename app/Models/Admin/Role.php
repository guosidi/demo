<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "admin_role";

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}

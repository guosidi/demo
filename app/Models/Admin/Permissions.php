<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    protected $table = "admin_permissions";

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

}

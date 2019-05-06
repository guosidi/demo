<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenArea extends Model
{
    protected $connection = 'mysql_recmall';
    protected $table = 'open_area';
    public $timestamps = false;

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    function getOpenProvince($province_id = 0)
    {
        $query = self::where('area_status', 1)
            ->select('province_id', 'province_name');
        if ($province_id) {
            $query = $query->where('province_id', $province_id);
        } else {
            $query = $query->where('id', '<>', 1);
        }
        return $query->groupBy('province_id')->get();
    }
}

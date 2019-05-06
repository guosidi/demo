<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Area extends Model
{
    protected $connection = 'mysql_recmall';
    protected $table = 'area';
    public $timestamps = false;

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param $canteen_id
     * @return \Illuminate\Support\Collection|mixed
     * 查询当前店铺位置下的所有市和区
     */
    public function selectCanteenaddress($canteen_id){
        $canteen = DB::connection($this->connection)
            ->table('canteen')
            ->where('canteen_id',$canteen_id)
            ->select('province_id','province_name')
            ->first();

        //获取当前店铺所在位置下的所有市
        $city = DB::connection($this->connection)
            ->table($this->table)
            ->where('parentid',$canteen->province_id)
            ->select('id','shortname','parentid')
            ->get();
        $city = json_decode(json_encode($city),true);
        $city_id = array_column($city,'id');
        //获取当前店铺所在位置下的所有区
        $zone = DB::connection($this->connection)
            ->table($this->table)
            ->whereIn('parentid',$city_id)
            ->select('id','shortname','parentid')
            ->get();
        $zone = json_decode(json_encode($zone),true);
        foreach ($city as $key => $val ){
            foreach ($zone as $k => $v ){
                if ($v['parentid'] == $val['id']){
                    $city[$key]['son'][] = $v;
                }
            }
        }

        return $city;
    }
}

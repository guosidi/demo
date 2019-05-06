<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/20 0020
 * Time: 10:46
 */
namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Order;

class User extends Model
{
    protected $table = "user_address";
//    public $timestamps = false;

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param $userAddress
     * @return mixed
     * 添加或修改收货地址
     */
    public function add_address($userAddress){
        $data = DB::table('user_address')
            ->where('canteen_id',$userAddress['canteen_id'])
            ->first();
        $areaname= Order::instance()->getAaeaById('area',[],['id','areaname'],['id','data'=>[$userAddress['city_id'],$userAddress['district_id'],$userAddress['province_id']]]);
        $areaname=json_decode(json_encode($areaname),true);
        $userAddress['province_name'] = $areaname[0]['areaname'];
        $userAddress['city_name'] = $areaname[1]['areaname'];
        $userAddress['zone_name'] = $areaname[2]['areaname'];
        if (empty($data)){
            $address_id = DB::table('user_address')->insertGetId($userAddress);
            $userAddress = DB::table('user_address')
                ->where('address_id',$address_id)
                ->select('address_id','consignee','province_id','city_id','district_id','address','mobile')
                ->first();
        }else{
            DB::table('user_address')->where('canteen_id',$userAddress['canteen_id'])->update($userAddress);
        }

        return json_decode(json_encode($userAddress),true);
    }
}
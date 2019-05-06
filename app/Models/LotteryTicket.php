<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
class LotteryTicket extends Model
{
    use SoftDeletes;
    protected $dateFormat = 'U';
    protected $table = 'lottery_tickets';  //定义用户表名称
    protected $dates = ['deleted_at'];
    public function getProvince(){
        $provinceList = DB::connection('mysql_recmall')->table('open_area')->whereNotNull('province_name')->get();
        return $provinceList;
    }

    /**
     * @param $canteen_id
     * @return \Illuminate\Support\Collection|mixed
     * 查询在售彩票列表
     */
    public function selectLottery($canteen_id){
        $province_id = DB::connection('mysql_recmall')->table('canteen')->where('canteen_id',$canteen_id)->value('province_id');
        $lottery = DB::table('lottery_tickets')
            ->where('province_id',$province_id)
            ->where('status',1)
            ->where('remain','>',0)
            ->whereNull('deleted_at')
            ->select('id','number','pic','name','summary','price')
            ->get();
        $lottery = json_decode(json_encode($lottery),true);
        foreach ($lottery as $key => $val ){
            $lottery[$key]['default_number'] = 0;
        }
        return $lottery;
    }
    
}

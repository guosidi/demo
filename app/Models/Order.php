<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19 0019
 * Time: 16:14
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tools\Verify;
use DB;

class Order extends Model
{

    protected $table = "orders";

    protected $primaryKey = 'order_id';

    protected $casts = [
        'order_id' => 'string'
    ];

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public static function array_unset_tt($arr, $key)
    {
        //建立一个目标数组
        $res = array();
        foreach ($arr as $value) {
            //查看有没有重复项
            if (isset($res[$value[$key]])) {
                //有：销毁
                unset($value[$key]);
            } else {
                $res[$value[$key]] = $value;
            }
        }
        $res = array_values($res);

        return $res;
    }

    /**
     * @param $order_status
     * @param $canteen_id
     * @return \Illuminate\Support\Collection|mixed
     * 查询订单查询列表
     */
    public function selectOrderList($order_status,$canteen_id){
        $orderList = DB::table('orders')
            ->where('canteen_id',$canteen_id);
        if ($order_status == 0 ){
            $orderList->whereNotNull('order_status');
        }else{
            $orderList->where('order_status',$order_status);
        }
        $orderList = $orderList->where('is_pay',1)->select('order_id','order_status','pay_fee','canteen_id','created_at','payed_at')->get();
        $orderList = json_decode(json_encode($orderList),true);
        if (!empty($orderList)){
            $order_id = array_column($orderList,'order_id');
            $order_id_str = implode(',',$order_id);
            $orderGoods = DB::select("select sum(goods_number) as nums,order_id from orders_goods where order_id in ($order_id_str) GROUP BY order_id");
            $orderGoods = json_decode(json_encode($orderGoods),true);
            $orderGoodsName = DB::table('orders_goods')
                ->whereIn('order_id',$order_id)
                ->select('order_id','lottery_name')
                ->get();
            $orderGoodsName = json_decode(json_encode($orderGoodsName),true);
            $new_GoodsName = [];
//            print_r($orderGoodsName);

            foreach ($orderGoodsName as $data) {
                isset($new_GoodsName[$data['order_id']]) || $new_GoodsName[$data['order_id']] = array();
                $new_GoodsName[$data['order_id']][] = $data['lottery_name'];
            }
//            print_r($new_GoodsName);die;
            $newData = [];
            foreach ($new_GoodsName as $key => $val ){
                $newData[$key]['order_id'] = '';
                $newData[$key]['lottery_name'] = '';
                foreach ($val as $k => $v ){
                    $newData[$key]['order_id'] = $key;
                    $newData[$key]['lottery_name'] .= $v.'、';
                }
            }
//            var_dump($newData);die;
            foreach ($newData as $key => $val ){
                $newData[$key]['lottery_name'] = mb_substr($newData[$key]['lottery_name'],0,-1,'utf-8');
            }

            $new_GoodsName = array_values($newData);
//        print_r($new_GoodsName);die;
            foreach ($orderList as $key => $val ){
                $orderList[$key]['is_collect'] = 0;
                if ($val['order_status'] == 2 ){
                    $orderList[$key]['is_collect'] = 1;
                }
                if ($val['order_id'] == $orderGoods[$key]['order_id']){
                    $orderList[$key]['goods_number'] = $orderGoods[$key]['nums'];
                }
                foreach ($new_GoodsName as $k => $v ){
                    if ($val['order_id'] == $v['order_id']){
                        $orderList[$key]['lottery_name'] = $v['lottery_name'];
                    }
                }
                $orderList[$key]['payed_at'] = date('Y-m-d H:i:s',$val['payed_at']);
            }
            array_multisort(array_column($orderList, 'payed_at'), SORT_DESC, $orderList);
        }
        return $orderList;
    }

    /**
     * @param $order_id
     * @return Model|mixed|null|object|static
     * 查询订单详情
     */
    public function orderFirst($order_id){
        //订单信息
        $orderFirst = DB::table('orders as o')
            ->leftjoin('orders_shipping as os', 'o.order_id', '=', 'os.order_id')
            ->where('o.order_id',$order_id)
            ->select('o.order_id','o.created_at','o.province_name','o.city_name','o.zone_name','o.address','o.consignee','o.mobile','o.is_pay','o.pay_fee','o.order_status','os.shipping_name','os.shipping_code','o.remark','o.canteen_name')
            ->first();
        $orderFirst = json_decode(json_encode($orderFirst),true);
        if (!empty($orderFirst)){
            //订单商品信息
            $orderGoodsName = DB::table('orders_goods')
                ->where('order_id',$order_id)
                ->select('lottery_name','goods_price','goods_number')
                ->get();
            $orderGoodsName = json_decode(json_encode($orderGoodsName),true);

            $orderFirst['goods'] = $orderGoodsName;
        }

        return $orderFirst;
    }

    public static function getAaeaById($table,$where,$clum,$whereIn=null){

        if(!empty($whereIn)){

            return  DB::connection('mysql_recmall')->table($table)->whereIn($whereIn['0'],$whereIn['data'])->select($clum)->get();

        }else{
            return  DB::connection('mysql_recmall')->table($table)->where($where)->select($clum)->get()->toArray();
        }


    }

    /**
     * @param $cart
     * @return mixed
     * 提交购物车计算价格展示位置
     */
    public function settlementOrder($canteen_id,$lottery){
        try{
            $cart = [];
            $userAddress = DB::table('user_address')
                ->where('canteen_id',$canteen_id)
                ->select('address_id','canteen_id','consignee','province_id','city_id','district_id as zone_id','address','mobile')
                ->first();
            if (empty($userAddress)){
                $userAddress = DB::connection('mysql_recmall')
                    ->table('canteen')
                    ->where('canteen_id',$canteen_id)
                    ->select('province_id','province_name','city_id','city_name','zone_id','zone_name','address','telephone as mobile','canteen_name','personincharge as consignee','canteen_id')
                    ->first();
                $userAddress->address_id = 0;
            }else{
                $areaname= $this->getAaeaById('area',[],['id','areaname'],['id','data'=>[$userAddress->city_id,$userAddress->zone_id,$userAddress->province_id]]);
                $areaname=json_decode(json_encode($areaname),true);
                $canteen_name =  DB::connection('mysql_recmall')->table('canteen')->where('canteen_id',$canteen_id)->value('canteen_name');
                $userAddress->province_name = $areaname[0]['areaname'];
                $userAddress->city_name = $areaname[1]['areaname'];
                $userAddress->zone_name = $areaname[2]['areaname'];
                $userAddress->canteen_name = $canteen_name;
            }
            $userAddress = json_decode(json_encode($userAddress),true);
            $cart['canteen_id'] = $canteen_id;
            $cart['cart_info'] = $lottery;
            $cart['add'] = $userAddress;
            $price = [];
//            print_r($cart);die;
            foreach ($cart['cart_info'] as $key => $val ){
                $price[] = $val['lottery_number'] * $val['lottery_price'];
            }
            $cart['pay_fee'] = array_sum($price);
            unset($cart['address_id']);
        }catch (\Exception $e){
            return $e->getCode();
        }
        return $cart;
    }

    /**
     * @param $data
     * @return int|mixed
     * 提交订单
     */
    public function commitOrder($canteen_id,$goods,$pay_fee,$payment_id,$remark){
        try{
            foreach ($goods as $key => $val ){
                $lottery = DB::table('lottery_tickets')
                    ->where('id',$val['lottery_id'])
                    ->where('status',1)
                    ->whereNull('deleted_at')
                    ->first();
                $lottery = json_decode(json_encode($lottery),true);
                if (!empty($lottery)){
                    if ($val['lottery_number'] > $lottery['remain'] || $val['lottery_price'] != $lottery['price']){
                        return 305;
//                        throw new \Exception('商品信息已变更，请重新确认', 305);
                    }
                }else{
                    return 305;
//                    throw new \Exception('商品信息已变更，请重新确认', 305);
                }
            }
            //收货地址
            $userAddress = DB::table('user_address')
                ->where('canteen_id',$canteen_id)
                ->select('address_id','canteen_id','consignee','province_id','city_id','district_id as zone_id','address','mobile')
                ->first();
            if (empty($userAddress)){
                $userAddress = DB::connection('mysql_recmall')
                    ->table('canteen')
                    ->where('canteen_id',$canteen_id)
                    ->select('province_id','province_name','city_id','city_name','zone_id','zone_name','address','telephone as mobile','canteen_name','personincharge as consignee','canteen_id')
                    ->first();
                $userAddress->address_id = 0;
            }else{
                $areaname= $this->getAaeaById('area',[],['id','areaname'],['id','data'=>[$userAddress->city_id,$userAddress->zone_id,$userAddress->province_id]]);
                $areaname=json_decode(json_encode($areaname),true);
                $canteen_name =  DB::connection('mysql_recmall')->table('canteen')->where('canteen_id',$canteen_id)->value('canteen_name');
                $userAddress->province_name = $areaname[0]['areaname'];
                $userAddress->city_name = $areaname[1]['areaname'];
                $userAddress->zone_name = $areaname[2]['areaname'];
                $userAddress->canteen_name = $canteen_name;
            }
            $userAddress = json_decode(json_encode($userAddress),true);
            DB::beginTransaction();
            //订单主信息
            $inserOrder = [];
            $num = mt_rand(1000,9999);
            $inserOrder['order_id'] = '1' . time() . $num;
            $inserOrder['canteen_id'] = $canteen_id;
            $inserOrder['canteen_name'] = $userAddress['canteen_name'];
            $inserOrder['pay_fee'] = $pay_fee;
            $inserOrder['remark'] = $remark;
            $inserOrder['payment_id'] = $payment_id;
            $inserOrder['province_id'] = $userAddress['province_id'];
            $inserOrder['province_name'] = $userAddress['province_name'];
            $inserOrder['city_id'] = $userAddress['city_id'];
            $inserOrder['city_name'] = $userAddress['city_name'];
            $inserOrder['zone_id'] = $userAddress['zone_id'];
            $inserOrder['zone_name'] = $userAddress['zone_name'];
            $inserOrder['address'] = $userAddress['address'];
            $inserOrder['consignee'] = $userAddress['consignee'];
            $inserOrder['mobile'] = $userAddress['mobile'];
            $inserOrder['useradress_id'] = $userAddress['address_id'];
            $order = DB::table('orders')->insert($inserOrder);
//            print_r($order);die;
            //订单商品信息
            $inserOrderGoods = [];
            $goods_number = 0;
            foreach ($goods as $key => $val ){
                $inserOrderGoods['order_id'] = $inserOrder['order_id'];
                $inserOrderGoods['goods_price'] = $val['lottery_price'];
                $inserOrderGoods['goods_number'] = $val['lottery_number'];
                $inserOrderGoods['canteen_id'] = $canteen_id;
                $inserOrderGoods['lottery_id'] = $val['lottery_id'];
                $inserOrderGoods['lottery_name'] = $val['lottery_name'];
                $inserOrderGoods['total_price'] = $val['lottery_number']*$val['lottery_price'];
                $inserOrderGoods['default_img'] = $val['lottery_pic'];
                $order_goods = DB::table('orders_goods')->insert($inserOrderGoods);
//                $remain = DB::table('lottery_tickets')->where('id',$val['lottery_id'])->value('remain');
//                $new_remain = $remain - $val['lottery_number'];
//                $del_goods_remain = DB::table('lottery_tickets')
//                    ->where('id',$val['lottery_id'])
//                    ->update(['remain'=> $new_remain ]);
                if (!$order_goods ){
                    $goods_number += 1;
                }
            }

            if ($order && $goods_number == 0){
                DB::commit();
            }else{
                DB::rollBack();
                throw new \Exception( '提交失败', 300 );
            }
        }catch (\Exception $e){
            return $e->getCode();
        }

        $newData = [];
        $newData['order_id'] = $inserOrder['order_id'];
        $newData['payment_id'] = $payment_id;

        return $newData;
    }

    /**
     * @param $order_id
     * @param $is_collect
     * @return bool
     * 订单确认收货
     */
    public function collectGoods($order_id,$is_collect){
        DB::table('orders')
            ->where('order_id',$order_id)
            ->update(['order_status'=> $is_collect,"updated_at"=> date('Y-m-d H:i:s',time()) ]);
        return true;
    }

    /**
     * @param $goods
     * @return int
     * 点击去结算时检测商品状态
     */
    public function TestingGoods($goods){
        foreach ($goods as $key => $val ){
            $lottery = DB::table('lottery_tickets')
                ->where('id',$val['lottery_id'])
                ->where('status',1)
                ->whereNull('deleted_at')
                ->first();
            $lottery = json_decode(json_encode($lottery),true);
            if (!empty($lottery)){
                if ($val['lottery_number'] > $lottery['remain'] || $val['lottery_price'] != $lottery['price']){
                    return 305;
//                    throw new \Exception('商品信息已变更，请重新确认', 305);
                }else{
                    return 200;
                }
            }else{
                return 305;
            }
        }
    }


    public function TestingStock($order_id){
        $goods_stock = DB::table('orders_goods')->where('order_id',$order_id)->select('lottery_id','goods_number')->get();
        $goods_stock = json_decode(json_encode($goods_stock),true);
        $lottery_id = array_column($goods_stock,'lottery_id');
        $remain = DB::table('lottery_tickets')->whereIn('id',$lottery_id)->select('id','remain')->get();
        $remain = json_decode(json_encode($remain),true);
        foreach ($goods_stock as $key => $val ){
            if ($val['goods_number'] > $remain[$key]['remain']){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 获取订单，此订单应为未支付
     * @param unknown $order_id
     * @return unknown
     */
    
    public static function getOrderByIdFirst($order_id){
        
        return DB::table('orders')->where(['order_id'=>$order_id])->first();
        
    }
    
    /**
     * 获取订单，此订单应为未支付.不用写活方法了，固定死，好找问题，payment_id 应该由这里做最后决定
     * @param unknown $order_id
     * @return unknown
     */
    
    public  function updateOrderPayed($order_id,$payment_id=1,$pay_code = null){

        $data =  DB::table('orders')->where(['order_id'=>$order_id,'is_pay'=>0])->update(['is_pay'=>1,'order_status'=>1,'payment_id'=>$payment_id,'payed_at'=>time(),'pay_code'=>$pay_code]);
        $content_add='';
        //有时间在期监听事件
        if($data==1){   
            //修改库存
            $goodslist = $this->updateRemain($order_id);
       
            if(!empty($goodslist)){
               $numbers = DB::table('lottery_tickets')->select('name','remain')->whereIn('id',$goodslist)->get();
               foreach ($numbers as $k=>$v){
                   $content_add.=$v->name.",剩余".$v->remain.";";
               }
            }
            $sql_users =  "select a.phone,o.canteen_name from admin as a left join orders AS o on o.province_id =a.province_id where o.order_id= $order_id";
            $msglist=DB::select($sql_users);
            
           foreach ($msglist as $k=>$v){
               Verify::instance()->sendMsgcp($v->phone,$cpname='彩票',$v->canteen_name,$type=10,$content_add);
           }
        }
        return $data;
    }
    
    
    public function testaa($order_id){
        
        $content_add='';
        //$goodslist = $this->updateRemain($order_id);
        $goodslist=[1,2];
        if(!empty($goodslist)){
            $numbers = DB::table('lottery_tickets')->select('name','number')->whereIn('id',$goodslist)->get();
            foreach ($numbers as $k=>$v){
                $content_add.=$v->name."剩余".$v->number.";";
            }
        } 
       
        $username = DB::table("orders")->where('order_id',$order_id)->select('canteen_name','province_id')->first();
        
        $sql_users =  "select a.phone,o.canteen_name from admin as a left join orders AS o on o.province_id =a.province_id where o.order_id= $order_id";
        
        $msglist=DB::select($sql_users);
      
        foreach ($msglist as $k=>$v){
          
            Verify::instance()-> sendMsgcp($v->phone,$cpname='彩票',$v->canteen_name,$type=10,$content_add);
        
        }
    }
    
    //修改库存,只做修改负数不管，返回goods列表
    private function updateRemain($order_id){
       $list= DB::table('orders_goods')->where('order_id',$order_id)->select('lottery_id','goods_number')->get();
          $goods=[];
          foreach ($list as $k=>$v){
              
              $goods[]=$v->lottery_id;
              $sql =" update `lottery_tickets` set `remain` = remain-".intval($v->goods_number)." where `id` = ".$v->lottery_id;
             if(DB::update($sql)) {
                 error_log(date("Y-m-d H:i:s")."====".$order_id."====ccccc".$sql."-======".json_encode($order_id)."\n",3,base_path("storage/logs")."/Alipay/222222".date("Y-m-d").".log");
                 
             }else{
                 error_log(date("Y-m-d H:i:s")."====".$order_id."====ddddd".$sql."-======".json_encode($order_id)."\n",3,base_path("storage/logs")."/Alipay/222222".date("Y-m-d").".log");
                 
             }
          }
          return $goods;
    }
 
    
    
}
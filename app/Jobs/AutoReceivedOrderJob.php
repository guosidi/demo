<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AutoReceivedOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
        //error_log(date("Y-m-d H:i:s").$this->order_id."\n",3,base_path("storage/logs")."/0126/".date("Y-m-d").".log");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //error_log(date("Y-m-d H:i:s").$this->order_id."\n",3,base_path("storage/logs")."/0126/".date("Y-m-d").".log");
        $order_id = $this->order_id;
        $info = Order::find($order_id);
        if ($info && $info->order_status == 2) {
            $info->order_status = 3;
            $info->received_at = time();
            if ($info->save()) {
                Log::channel('received')->info('[' . $order_id . ']自动确认收货成功');
            } else {
                Log::channel('received')->info('[' . $order_id . ']自动确认收货失败');
            }
        }
        /*$orders = DB::table('orders')->where('order_id', $order_id)->where('order_status', '3')->first();//是否已经有过确认收货
        $order_status = DB::table('orders')->where('order_id', $order_id)->value('order_status');//是否是配送中的状态
        if (!$orders && $order_status == 2) {
            $res = DB::table('orders')->where('order_id', $order_id)->update([
                'order_status' => '3',
                'updated_at' => time(),
                'received_at' => time()
            ]);
        }
        return true;*/
    }
}

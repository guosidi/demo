<?php
/**
 * Created .
 * User: Hkj
 * Date: 2017/4/12
 * Time: 14:49
 */
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Payment_logModel extends Model
{

    protected $table = "payment_log";

    private static $instance;

    protected $dateFormat = 'U';//时间格式为int类型
    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new Payment_logModel();
        return self::$instance;
    }


}
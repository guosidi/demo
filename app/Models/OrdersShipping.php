<?php
/**
 * Created by PhpStorm.
 * User: guosidi
 * Date: 2018/9/20
 * Time: 15:21
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersShipping extends Model
{
    protected $table = "orders_shipping";
    protected $primaryKey = 'shipping_id';

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}
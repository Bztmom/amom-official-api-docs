<?php
/**
 * Created by PhpStorm.
 * User: mac126
 * Date: 2018/7/31
 * Time: 下午4:30
 */

//定义参数

define('ACCESS_KEY','');
define('SECRET_KEY','');
define('VERSION','V1.0');

include "lib.php";

$rs = new req();

$rs->get_UactiveOrder('usdt_btc');



<?php
/**
 *
 * Amom api
 * Date: 2018/7/31
 * Time: 下午3:45
 */

class req
{
    private $api = 'https://api.a.mom';

    protected $api_method = '';
    protected $req_method = 'get';
    protected $url = '';
    protected $headers = [];
    protected $params = [];

    /**
     * 获取聚合详情列表
     *
     * @param $symbol
     * @return string
     */
    public function get_ticker($symbol = '')
    {
        $this->api_method = '/api/open/ticker';

        // 可选参数获取指定交易对信息
        $param = [
            'symbol' => $symbol
        ];

        return $this->set_params($param)->set_url($this->params)->curl();

    }

    /**
     * 查询所有交易对
     *
     * @return mixed
     */
    public function get_symbol()
    {
        $this->api_method = '/api/open/symbol';

        return $this->set_url($this->params)->curl();

    }

    /**
     * 获取币种成交记录
     *
     * @param $symbol
     * @return string
     */
    public function get_dealOrders($symbol)
    {

        $this->api_method = '/api/open/dealOrders';

        // 必选参数获取指定交易对信息
        $param = [
            'symbol' => $symbol
        ];

        return $this->set_params($param)->set_url($this->params)->curl();
    }

    /**
     * 获取币种深度
     *
     * @param $symbol
     * @return mixed
     */
    public function get_depth($symbol)
    {
        $this->api_method = '/api/open/depth';

        // 可选参数获取指定交易对信息
        $param = [
            'symbol' => $symbol
        ];

        return $this->set_params($param)->set_url($this->params)->curl();

    }

    // 以下验证请求需要身份验证

    /**
     * 获取个人用户挂单信息
     * 请求类型 GET
     * @param $symbol
     * @return mixed
     */
    public function get_UactiveOrder($symbol)
    {
        $this->api_method = '/api/spot/activeOrders';

        // 必须参数获取指定交易对信息
        $param = [
            'symbol' => $symbol,
            'timestamp' => $this->getMillisecond()
        ];

        return $this->set_params($param)->set_url($this->params)->set_headers()->curl();
    }


    /**
     * 获取自己已交易的订单
     *
     * @param $symbol   交易对
     * @param int $p 页码
     * @return mixed
     */
    public function get_UdealOrders($symbol, $p = 1)
    {
        $this->api_method = '/api/spot/dealOrders';
        // 必须参数获取指定交易对信息
        $param = [
            'symbol' => $symbol,
            'p' => $p,
            'timestamp' => $this->getMillisecond()
        ];

        return $this->set_params($param)->set_url($this->params)->set_headers()->curl();
    }

    /**
     * 获取订单详情
     *
     * @param $symbol
     * @param $orderId
     * @return mixed
     */
    public function get_UorderView($symbol, $orderId)
    {
        $this->api_method = '/api/spot/orderView';
        $param = [
            'orderId' => $orderId,
            'symbol' => $symbol,
            'timestamp' => $this->getMillisecond()
        ];

        return $this->set_params($param)->set_url($this->params)->set_headers()->curl();
    }


    /**
     * 取消挂单
     *
     * @param $symbol
     * @param $orderId
     * @return mixed
     */
    public function post_cencelOrder($symbol, $orderId)
    {
        $this->req_method = 'POST';
        $this->api_method = '/api/spot/cancelOrder';
        $param = [
            'orderId' => $orderId,
            'symbol' => $symbol,
            'timestamp' => $this->getMillisecond()
        ];

        return $this->set_params($param)->set_url()->set_headers()->curl();
    }


    /**
     * 设置挂单
     *
     * @param $symbol
     * @param $amount
     * @param $price
     * @param $type
     * @return mixed
     */
    public function post_createOrder($symbol, $amount, $price, $type)
    {
        $this->req_method = 'POST';
        $this->api_method = '/api/spot/createOrder';

        $param = [
            'price' => $price,
            'amount' => $amount,
            'symbol' => $symbol,
            'type' => $type,
            'timestamp' => $this->getMillisecond()
        ];

        return $this->set_params($param)->set_url()->set_headers()->curl();
    }


    /**
     * 设置请求地址
     *
     * @param array $param
     * @return $this
     */
    protected function set_url($param = [])
    {
        $this->url = $this->api . $this->api_method . '?' . http_build_query($param, '&');
        return $this;
    }


    /**
     * 设置提交参数params
     *
     * @param array $param
     * @return $this
     */
    protected function set_params($param = [])
    {
        $param['signature'] = hash_hmac('sha256', http_build_query($param, '', '&'), md5(SECRET_KEY));
        $this->params = $param;
        return $this;
    }


    /**
     * 设置请求头
     *
     * @return $this
     */
    protected function set_headers()
    {
        $this->headers[] = 'Api-key: ' . ACCESS_KEY;
        $this->headers[] = 'Api-version: ' . VERSION;
        return $this;
    }

    /**
     * 发送http请求
     *
     * @return mixed
     */
    protected function curl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        if ($this->req_method == 'POST' && !empty($this->params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    /**
     * 获取毫秒时间戳
     *
     * @return float
     */
    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}


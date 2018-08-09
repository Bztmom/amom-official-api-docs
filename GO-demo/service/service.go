package service

import (
	"io/ioutil"
	"net/http"
	"config"
	"time"
	"strconv"
	"crypto/hmac"
	"crypto/sha256"
	"fmt"
	"strings"
)

func Get_tickers(symbol ...string) string {
	params := make(map[string]string)

	if (len(symbol) > 0) {
		params["symbol"] = symbol[0]
	}
	url := "/api/open/ticker"
	return HttpRequest("GET", url, params)
}

func Get_symbol() string {
	url := "/api/open/symbol"
	return HttpRequest("GET", url, make(map[string]string))
}

func Get_dealOrders(symbol string) string {
	params := make(map[string]string)
	url := "/api/open/dealOrders"
	params["symbol"] = symbol
	return HttpRequest("GET", url, params)
}

func Get_depth(symbol string) string {
	params := make(map[string]string)
	url := "/api/open/depth"
	params["symbol"] = symbol
	return HttpRequest("GET", url, params)
}

func Get_UactiveOrder(symbol string) string {
	keyList := []string{"symbol", "timestamp"}
	params := make(map[string]string)
	url := "/api/spot/activeOrders"
	params["symbol"] = symbol
	params["timestamp"] = GetTime()
	params["signature"] = SH256(params, config.SECRET_KEY, keyList)

	return HttpRequest("GET", url, params)
}

func Get_UdealOrders(symbol string, p int) string {
	keyList := []string{"symbol", "p", "timestamp"}
	params := make(map[string]string)
	url := "/api/spot/dealOrders"
	params["symbol"] = symbol
	params["p"] = strconv.Itoa(p)
	params["timestamp"] = GetTime()
	params["signature"] = SH256(params, config.SECRET_KEY, keyList)

	return HttpRequest("GET", url, params)
}

/**
	订单详情
	交易对
	订单id
 */
func Get_UorderView(symbol string, orderId string) string {
	keyList := []string{"orderId", "symbol", "timestamp"}
	params := make(map[string]string)
	url := "/api/spot/orderView"
	params["symbol"] = symbol
	params["orderId"] = orderId
	params["timestamp"] = GetTime()
	params["signature"] = SH256(params, config.SECRET_KEY, keyList)

	return HttpRequest("GET", url, params)
}

/**
	取消挂单
	交易对
	订单id
 */
func Post_cencelOrder(symbol string, orderId string) string {
	keyList := []string{"orderId", "symbol", "timestamp"}
	params := make(map[string]string)
	url := "/api/spot/cancelOrder"
	params["symbol"] = symbol
	params["orderId"] = orderId
	params["timestamp"] = GetTime()
	params["signature"] = SH256(params, config.SECRET_KEY, keyList)

	return HttpRequest("POST", url, params)
}

/**
	创建订单
	symbol 交易对
	amount	交易数量
	price	交易金额
	t		交易状态 	sell 卖| buy 买
 */
func Post_createOrder(symbol string, amount string, price string, t string) string {
	params := make(map[string]string)
	keyList := []string{"price", "amount", "symbol", "type", "timestamp"}
	url := "/api/spot/createOrder"
	params["symbol"] = symbol
	params["amount"] = amount
	params["price"] = price
	params["type"] = t
	params["timestamp"] = GetTime()

	params["signature"] = SH256(params, config.SECRET_KEY, keyList)

	return HttpRequest("POST", url, params)
}

// 发送get请求
// t 请求类别  get post
// url 	接口请求地址
// params 地址栏参数
// headers 请求头参数
func HttpRequest(t string, url string, params map[string]string) string {
	api_url := ""
	if (t == "GET") {
		api_url = CreateUrl(url, params)
	} else {
		api_url = CreateUrl(url, make(map[string]string))
	}

	httpClient := &http.Client{}

	request, err := http.NewRequest(t, api_url, strings.NewReader(postData(params)))
	if nil != err {
		return err.Error()
	}

	request.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	request.Header.Add("api-key", config.ACCESS_KEY)
	request.Header.Add("api-version", "V1.0")

	response, err := httpClient.Do(request)

	if nil != err {
		return err.Error()
	}

	defer response.Body.Close()

	body, err := ioutil.ReadAll(response.Body)

	if nil != err {
		return err.Error()
	}
	return string(body)
}

//	创建请求地址
func CreateUrl(method string, params map[string]string) string {

	return config.API_HOST + method + "?" + postData(params)
}

// 加密
func SH256(params map[string]string, key string, keyList []string) string {

	serach := []byte(key)
	h := hmac.New(sha256.New, serach)

	h.Write([]byte(toString(params, keyList)))

	return fmt.Sprintf("%x", h.Sum(nil))
}

// 返回毫秒时间戳
func GetTime() string {
	return fmt.Sprint("%x", time.Now().UnixNano()/1e6)
}

// 获取post参数字符串
func postData(params map[string]string) string {
	data := ""
	for k, v := range params {
		data = data + "&" + k + "=" + v
	}
	if ("" != data) {
		data = data[1:]
	}
	return data
}

// 根据参数拼接字符串
func toString(params map[string]string, keyList []string) string {

	t := time.Now()

	params["timestamp"] = strconv.FormatInt(t.UnixNano()/1000000, 10)

	data := "";
	for _, k := range keyList {
		data = data + "&" + k + "=" + params[k]
	}

	if ("" != data) {
		data = data[1:]
	}
	return data
}

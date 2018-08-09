# !/usr/bin/env python2.7
# -*- coding=utf-8 -*-

import sys
import time

import requests
import hashlib
import urllib
import hmac
import collections

reload(sys)
sys.setdefaultencoding('utf-8')


class Mom:
    api = 'https://api.a.mom'
    api_method = ''

    url = ''
    headers = {}
    params = {}

    def __init__(self,
                 access_key,
                 secret_key,
                 timeout=30
                 ):
        self.access_key = access_key
        self.secret_key = secret_key
        self.timeout = timeout

    # 获取聚合详情列表
    def get_ticker(self, symbol=''):
        self.api_method = '/api/open/ticker'
        params = {
            'symbol': symbol,
        }
        return self.set_url(params).http_get().content

    # 获取交易对列表
    def get_symbol(self):
        self.api_method = '/api/open/symbol'
        return self.set_url().http_get().content

    # 获取币种交易数据
    def get_dealOrders(self, symbol):
        self.api_method = '/api/open/dealOrders'
        params = {
            'symbol': symbol,
        }
        return self.set_url(params).http_get().content

    # 获取币种深度
    def get_depth(self, symbol):
        self.api_method = '/api/open/depth'
        params = {
            'symbol': symbol
        }
        return self.set_url(params).http_get().content

    # ---------------- 以下请求需要身份验证 ----------------------

    # 获取个人用户挂单信息
    def get_UactiveOrder(self, symbol):
        self.api_method = '/api/spot/activeOrders'
        param = collections.OrderedDict()
        param["symbol"] = symbol
        param["timestamp"] = int(round(time.time() * 1000))
        return self.set_params(param).set_url().set_header().http_post().content

    # 获取成交订单
    def get_UdealOrders(self, symbol, p=1):
        self.api_method = '/api/spot/dealOrders'
        param = collections.OrderedDict()
        param["symbol"] = symbol
        param["p"] = p
        param["timestamp"] = int(round(time.time() * 1000))
        return self.set_params(param).set_url().set_header().http_post().content

    # 获取自己已交易的订单
    def get_UorderView(self, symbol, orderId):
        self.api_method = '/api/spot/orderView'
        param = collections.OrderedDict()
        param["orderId"] = orderId
        param["symbol"] = symbol
        param["timestamp"] = int(round(time.time() * 1000))
        return self.set_params(param).set_url().set_header().http_post().content

    # 取消订单
    def post_cencelOrder(self, symbol, orderId):
        self.api_method = '/api/spot/cancelOrder'
        param = collections.OrderedDict()
        param["orderId"] = orderId
        param["symbol"] = symbol
        param["timestamp"] = int(round(time.time() * 1000))
        return self.set_params(param).set_url().set_header().http_post().content

    # 创建交易订单
    def post_createOrder(self, symbol, amount, price, type):
        self.api_method = '/api/spot/createOrder'
        param = collections.OrderedDict()
        param["price"] = price
        param["amount"] = amount
        param["symbol"] = symbol
        param["type"] = type
        param["timestamp"] = int(round(time.time() * 1000))
        return self.set_params(param).set_url().set_header().http_post().content

    # ----------------- 以下工具方法 ------------------

    def set_params(self, params=None):
        if params is None:
            params = {}
        params['signature'] = hmac.new(self.secret_key, urllib.urlencode(params), digestmod=hashlib.sha256).hexdigest()
        self.params = params
        return self

    # 设置请求头信息
    def set_header(self):
        self.headers['Api-key'] = self.access_key
        self.headers['Api-version'] = "V1.0"
        return self

    # 设置请求url
    def set_url(self, params=None):
        print params
        if params is None:
            params = {}
        self.url = self.api + self.api_method + '?' + urllib.urlencode(params)
        return self

    # get获取数据
    def http_get(self):
        return requests.get(self.url, timeout=self.timeout, headers=self.headers)

    # post获取数据
    def http_post(self):
        return requests.post(self.url, timeout=self.timeout, headers=self.headers, data=self.params)

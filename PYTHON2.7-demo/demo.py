# !/usr/bin/env python2.7
# -*- coding=utf-8 -*-

import Service

access_key = ''
secret_key = ''
s = Service.Mom(access_key, secret_key)

print s.get_ticker()

#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
from configparser import ConfigParser

import fire
import requests
import memcache


BASE_PATH = os.path.dirname(os.path.abspath(__file__))


def load_config():
    global burl, corp_id, agent_id, contacts_secret_id, helper_secret_id
    conf = ConfigParser()
    conf.read(BASE_PATH + '/weixin.conf')
    burl = conf['DEFAULT']['burl']
    corp_id = conf['DEFAULT']['corp_id']
    agent_id = conf['DEFAULT']['agent_id']
    helper_secret_id = conf['DEFAULT']['helper_secret_id']
    contacts_secret_id = conf['DEFAULT']['contacts_secret_id']


load_config()


class Weixin:

    def __init__(self):
        self.mc = memcache.Client(['127.0.0.1:11211'])
        self.flush_token(False)

    def flush_token(self, nocache=True):
        self.contacts_token = self.__get_token(
            'wxcontacttoekn', contacts_secret_id, nocache)
        self.helper_token = self.__get_token(
            'wxhelpertoken', helper_secret_id, nocache)

    def __get_token(self, mckey, secret_id, nocache=False):
        token = self.mc.get(mckey)
        if not token or nocache:
            print('[+] getting cache... token key is {token}'.format(token=mckey))
            url = burl + 'gettoken?corpid={id}&corpsecret={secrect}'\
                .format(id=corp_id, secrect=secret_id)
            r = requests.get(url)
            if r.status_code == 200 and r.json().get('access_token'):
                token = r.json()['access_token']
            print('[+] dump token cache')
            self.mc.set(mckey, token, 7100)
        return token

    @staticmethod
    def get_post_body(**kwargs):
        """获取企业微信post数据体(json)"""
        return json.dumps(kwargs)

    def get_dep_list(self, depid=0):
        """获取部门列表"""
        url = burl + 'department/list?access_token={token}&id={depid}'\
            .format(token=self.contacts_token, depid=depid)
        r = requests.get(url)
        return r.json()

    def get_dep_users(self, depid=0, simple=True, fetch_child=0):
        """获取指定部门用户信息"""
        _type = 'simplelist' if simple else 'list'
        url = burl + 'user/{list}?access_token={token}&department_id={depid}&fetch_child={fetch}'\
            .format(list=_type, token=self.contacts_token, depid=depid, fetch=fetch_child)
        r = requests.get(url)
        return r.json()

    def get_user_info(self, userid):
        """获取用户信息"""
        url = burl + 'user/get?access_token={token}&userid={userid}'\
            .format(token=self.contacts_token, userid=userid)
        r = requests.get(url)
        return r.json()

    def get_app(self, agentid):
        """获取企业微信应用信息"""
        url = burl + 'agent/get?access_token={token}&agentid={agentid}'\
            .format(token=self.contacts_token, agentid=agentid)
        r = requests.get(url)
        return r.json()

    def create_dep(self, name, parentid, depid=0):
        """创建部门"""
        body = self.get_post_body(name=name, parentid=parentid, id=depid)
        url = burl + 'department/create?access_token={token}'\
            .format(token=self.contacts_token)
        r = requests.post(url, data=body)
        return r.json()

    def update_dep(self, depid, name):
        """更新部门信息"""
        body = self.get_post_body(id=depid, name=name)
        url = burl + 'department/update?access_token={token}'\
            .format(token=self.contacts_token)
        r = requests.post(url, data=body)
        return r.json()

    def delete_dep(self, depid):
        """删除部门"""
        url = burl + 'department/delete?access_token={token}&id={depid}'\
            .format(token=self.contacts_token, depid=depid)
        r = requests.get(url)
        return r.json()

    def create_user(self, userid, name, deps, ename=None, mobile=None,
                    title=None, gender=None, email=None, isleader=None,
                    enable=1, *args, **kwargs):
        """创建用户"""
        if mobile:
            mobile = str(mobile)
        body = self.get_post_body(userid=userid, name=name, english_name=ename,
                                  mobile=mobile, department=deps,
                                  position=title, gender=gender,
                                  email=email, isleader=isleader, enable=enable
                                  )
        url = burl + 'user/create?access_token={token}'\
            .format(token=self.contacts_token)
        r = requests.post(url, data=body)
        return r.json()

    def update_user(self, userid, name=None, deps=None, ename=None,
                    mobile=None, title=None, gender=None, email=None,
                    isleader=None, enable=1, *args, **kwargs):
        """更新用户信息"""
        if mobile:
            mobile = str(mobile)
        body = self.get_post_body(userid=userid, name=name, english_name=ename,
                                  mobile=mobile, department=deps,
                                  position=title, gender=gender,
                                  email=email, isleader=isleader, enable=enable
                                  )
        url = burl + 'user/update?access_token={token}'\
            .format(token=self.contacts_token)
        r = requests.post(url, data=body)
        return r.json()

    def delete_user(self, userid):
        """删除用户"""
        url = burl + 'user/delete?access_token={token}&userid={userid}'\
            .format(token=self.contacts_token, userid=userid)
        r = requests.get(url)
        return r.json()

    def delete_more_user(self, userid_list):
        """删除多个用户"""
        body = self.get_post_body(useridlist=userid_list)
        url = burl + 'user/batchdelete?access_token={token}'\
            .format(token=self.contacts_token)
        r = requests.post(url, data=body)
        return r.json()

    def send_message(self, content, touser=None, todep=None, safe=0):
        """发送企业微信文本消息给用户"""
        if isinstance(touser, list) or isinstance(touser, tuple):
            touser = '|'.join(touser)
        if isinstance(todep, list) or isinstance(todep, tuple):
            todep = '|'.join(todep)
        text = {'content': content}
        body = self.get_post_body(msgtype='text', agentid=0, text=text,
                                  touser=touser, toparty=todep, safe=safe
                                  )
        url = burl + 'message/send?access_token={token}'\
            .format(token=self.helper_token)
        r = requests.post(url, data=body)
        return r.json()


if __name__ == '__main__':
    fire.Fire(Weixin)

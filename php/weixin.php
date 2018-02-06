<?php

require __DIR__ . '/vendor/autoload.php';


/*************************************************
 *                  Consts                       *
 *************************************************/


// agentid
define('AGENT_ID', 123456);
// 企业id
define('CORP_ID', 'xxxxxxxxx');
// 企业小助手 secret id
define('HELPER_SECRET_ID', 'ABCDEFG');
// 通讯录 secret id
define('CONTACTS_SECRET_ID', 'ABCDEFG');




/*************************************************
 *                  Functions                    *
 *************************************************/


/**
 * HTTP GET Request
 *
 * @param string $url URL
 * 
 * @return object
 */
function get($url) {
    $r = Requests::get($url);
    return $r->success ? json_decode($r->body, true) : null;
}


/**
 * HTTP POST Reuqest
 *
 * @param string $url URL
 * @param array $headers header
 * @param array $data data
 * 
 * @return object
 */
function post($url, $headers=[], $data=[]) {
    $r = Requests::post($url, $headers, $data);
    return $r->success ? json_decode($r->body, true) : null;
}


/**
 * 获取 Token
 *
 * @param string $secret_id secret id
 * 
 * @return string
 */
function get_token($secret_id) {
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.CORP_ID.'&corpsecret='.$secret_id);
    return !empty($r) ? $r['access_token'] : null;
}


$helper_token = get_token(HELPER_SECRET_ID);
$contacts_token = get_token(CONTACTS_SECRET_ID);

if (!$helper_token || !$contacts_token) {
    die('Get token failed.');
}


/**
 * 获取部门列表
 *
 * @param integer $depid 部门ID
 * 
 * @return array
 */
function get_dep_list($depid=0) {
    global $contacts_token;
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token='.$contacts_token.'&id='.$depid);
    return $r;
}


/**
 * 获取部门用户
 *
 * @param integer $depid 部门ID
 * @param boolean $simple 用户信息是否为简单信息
 * @param integer $fetch_child 是否递归查询子部门用户
 * 
 * @return array
 */
function get_dep_users($depid=0, $simple=true, $fetch_child=1) {
    global $contacts_token;
    $type = $simple ? 'simplelist' : 'list';
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/user/'.$type.'?access_token='.$contacts_token.'&department_id='.$depid.'&fetch_child='.$fetch_child);
    return $r;
}


/**
 * 获取用户信息
 *
 * @param string $userid 用户ID
 * 
 * @return array
 */
function get_user_info($userid) {
    global $contacts_token;
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token='.$contacts_token.'&userid='.$userid);
    return $r;
}


/**
 * 创建部门
 *
 * @param integer $depid 部门ID
 * @param string $name 部门名字
 * @param integer $parentid 上级部门ID
 * 
 * @return array
 */
function create_dep($depid, $name, $parentid) {
    global $contacts_token;
    $body = json_encode(['name'=>$name, 'parentid'=>$parentid, 'id'=> $depid]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token='.$contacts_token, [], $body);
    return $r;
}


/**
 * 更新部门信息
 *
 * @param integer $depid 部门ID
 * @param string $name 部门名字
 * @param integer $parentid 上级部门ID
 * 
 * @return array
 */
function update_dep($depid, $name, $parentid) {
    global $contacts_token;
    $body = json_encode(['name'=>$name, 'parentid'=>$parentid, 'id'=> $depid]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token='.$contacts_token, [], $body);
    return $r;
}


/**
 * 删除部门
 *
 * @param integer $depid 部门ID
 * 
 * @return array
 */
function delete_dep($depid) {
    global $contacts_token;
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token='.$contacts_token.'&id='.$depid);
    return $r;
}


/**
 * 创建部门
 *
 * @param string $userid 用户ID
 * @param string $name 用户名
 * @param array $deps 所属部门ID
 * @param string $email 邮箱
 * @param string $mobile 手机号
 * @param string $ename 英文名字
 * @param string $title 岗位
 * @param string $gender 性别
 * @param integer $isleader 是否为领导
 * @param integer $enable 是否启用
 * 
 * @return array
 */
function create_user($userid, $name, $deps, $email=null, $mobile=null, $ename=null, $title=null, $gender=null, $isleader=null, $enable=1) {
    global $contacts_token;
    if ($mobile) $mobile = (string)$mobile;
    $body = json_encode(['userid'=>$userid, 'name'=>$name, 'english_name'=>$ename,
                        'mobile'=>$mobile, 'department'=>$deps, 'position'=>$title, 'gender'=>$gender,
                        'email'=>$email, 'isleader'=>$isleader, 'enable'=>$enable]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token='.$contacts_token, [], $body);
    return $r;
}


/**
 * 更新部门信息
 *
 * @param string $userid 用户ID
 * @param string $name 用户名
 * @param array $deps 所属部门ID
 * @param string $email 邮箱
 * @param string $mobile 手机号
 * @param string $ename 英文名字
 * @param string $title 岗位
 * @param string $gender 性别
 * @param integer $isleader 是否为领导
 * @param integer $enable 是否启用
 * 
 * @return array
 */
function update_user($userid, $name=null, $deps=null, $email=null, $mobile=null, $ename=null, $title=null, $gender=null, $isleader=null, $enable=1) {
    global $contacts_token;
    if ($mobile) $mobile = (string)$mobile;
    $body = json_encode(['userid'=>$userid, 'name'=>$name, 'english_name'=>$ename,
    'mobile'=>$mobile, 'department'=>$deps, 'position'=>$title, 'gender'=>$gender,
    'email'=>$email, 'isleader'=>$isleader, 'enable'=>$enable]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token='.$contacts_token, [], $body);
    return $r;
}


/**
 * 删除用户
 *
 * @param string $userid 用户ID
 * 
 * @return array
 */
function delete_user($userid) {
    global $contacts_token;
    $r = get('https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token='.$contacts_token.'&userid='.$userid);
    return $r;
}


/**
 * 删除多个用户
 *
 * @param array $userid_list 用户列表
 * 
 * @return array
 */
function delete_multi_user($userid_list) {
    global $contacts_token;
    $body = json_encode(['userlist'=>$userid_list]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token='.$contacts_token, [], $body);
    return $r;
}


/**
 * 发送消息
 *
 * @param string $content 发送内容
 * @param array/string $to_user 接收用户ID
 * @param array/string $to_dep 接收部门ID
 * @param integer $safe 是否为保密消息
 * 
 * @return array
 */
function send_message($content, $to_user, $to_dep=null, $safe=0) {
    global $helper_token;
    if (is_array($to_user))
        $to_user = join('|', $to_user);
    if (is_array($to_dep))
        $to_dep = join('|', $to_dep);
    $body = json_encode(['msgtype'=>'text', 'agentid'=>0, 'text'=>['content'=> $content], 'touser'=>$to_user, 'toparty'=>$to_dep, 'safe'=>$safe]);
    $r = post('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$helper_token, [], $body);
    return $r;
}


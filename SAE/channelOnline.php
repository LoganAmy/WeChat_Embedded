<?php
require 'send_msg.php';
/*
* 利用回调维护 channel 在线状态（储存在KDVB）
*
* channelOnline.php
* 放在根目录，并在config.yaml增加以下重定向
* ====================================
    handle:
     - rewrite: if( path ~ "(/_sae/channel/.*)" ) goto "channelOnline.php?callback=1&type=$1"
* ====================================
*    channel doc:
*      http://sae.sina.com.cn/doc/php/channel.html
*    rewrite doc:
*      http://sae.sina.com.cn/doc/php/runtime.html#url
*/

if($_GET['callback']==1)
{
    $type = $_GET['type'];                      //回调URL/回调类型
    $from = $_POST['from'];                     //回调接收的channel名
    $kv = new SaeKV();                          //初始化KVClient对象
    $ret = $kv->init();

    switch ($type)
    {
        case '/_sae/channel/connected':         //客户端连接
            if($kv->get('connect') == null)
            {
                $kv->set('connect','1');        //设置bananapi连接为 1
            }
            $kv->set('connect','1');
        break;
        case '/_sae/channel/disconnected':      //客户端断开
            $ret = $kv->set('connect', '0');
        break;
        case '/_sae/channel/message':           //客户端上行消息
            $message = $_POST['message'];
            $kv->set('flag','1');               //设置bananapi连接为 1
            msg_handing( $message );
            break;
    }
}

?>

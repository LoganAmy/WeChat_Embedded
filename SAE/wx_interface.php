<?php

include_once("wx_tpl.php");
require 'send_bpi.php';

$kv = new SaeKV();                      // 初始化KVClient对象
$ret = $kv->init();
if($kv->get('connect') == null)
    $kv->set('connect','0');            //bananapi未连接为 0
$status = $kv->get('connect');          //当前的连接状态

if($kv->get('flag') == null)
    $kv->set('flag','0');
$flag = $kv->get('flag');               //是否有消息返回


$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  //获取微信发送数据
if (!empty($postStr))
{
    //解析数据
    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    //发送消息方ID
    $fromUsername = $postObj->FromUserName;
    //接收消息方ID
    $toUsername = $postObj->ToUserName;
    //消息类型
    $form_MsgType = $postObj->MsgType;

    if($form_MsgType=="text")//文字消息
    {
        $form_Content = trim($postObj->Content);                //获取用户发送的文字内容
        if( $status ==0 || $flag ==0 )
        {
            $msgType = "text";
            $to_Content = 'BananaPi 不在线';
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
            echo $resultStr;
            exit;
        }

        $content = voice_msg( $form_Content );
        if( $content=="error")
        {
            $msgType = "text";
            $to_Content = "“" . $form_Content . "”\n对不起，系统不能理解您发送的内容/糗大了";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
            echo $resultStr;
        }
        else
        {
            echo '';
            //返回空字符串给微信服务器

            $channel = new SaeChannel();                //将用户名和消息发送给Bananapi
            $message = $fromUsername . "||" . $content;
            $ret = $channel->sendMessage('bananapi',$message);
            $kv->set('flag','0');
        }
        //$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $form_Content);
        //echo $resultStr;
        exit;
    }

    if($form_MsgType=="voice")//语音消息
    {
        if( $status ==0 || $flag ==0 )
        {
            $msgType = "text";
            $to_Content = 'BananaPi 不在线';
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
            echo $resultStr;
            exit;
        }
        if (isset($postObj->Recognition) && !empty($postObj->Recognition))
        {
            $content = voice_msg($postObj->Recognition);
            if( $content=="error")
            {
                $msgType = "text";
                $to_Content = "“" . $postObj->Recognition . "”\n对不起，系统不能理解您发送的内容/糗大了";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
                echo $resultStr;
            }
            else
            {
                echo '';
                //返回空字符串给微信服务器

                $channel = new SaeChannel();                //将用户名和消息发送给Bananapi
                $message = $fromUsername . "||" . $content;
                $ret = $channel->sendMessage('bananapi',$message);
                $kv->set('flag','0');
            }
        }
        else
        {
            $msgType = "text";
            $to_Content = "对不起，系统未能识别您输入的语音/糗大了";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
            echo $resultStr;
        }
        exit;
    }
    //事件消息
    if($form_MsgType=="event")
    {

        $form_Event = $postObj->Event;          //获取事件类型
        if($form_Event=="subscribe")            //订阅事件
        {
            $msgType = "text";                  //回复欢迎文字消息
            $contentStr = "关注成功";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $contentStr);
            echo $resultStr;
            exit;
        }
        elseif($form_Event=="CLICK")            //菜单点击
        {
            if( $status ==0 || $flag ==0 )
            {
                $msgType = "text";
                $to_Content = 'BananaPi 不在线';
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $to_Content);
                echo $resultStr;
                exit;
            }
            $form_EventKey =trim( $postObj->EventKey);
            echo '';
            //返回空字符串给微信服务器

            $channel = new SaeChannel();
            $message = $fromUsername . "||" . $form_EventKey;   //将用户名和消息发送给Bananapi
            $ret = $channel->sendMessage('bananapi', $message);
            $kv->set('flag','0');
            exit;
        }

    }
}
else
{
    echo "";
    exit;
}

?>
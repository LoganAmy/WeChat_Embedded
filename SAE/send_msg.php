<?php

function get_access_token()
{
    $kv = new SaeKV();                                      //初始化KVClient对象
    $ret = $kv->init();

    $past_access = $kv->get('access_token');                //判断access_token是否失效
    $str = explode(" ",$past_access) ;
    $pasttime = $str[0];
    $expires_in = $str[1];
    $access_token = $str[2];

    $test_time = time();
    if( ($test_time - $pasttime) > ($expires_in - 100) )    //如果access_token失效，重新获取
    {
        $appid = "wxeedb644c73908a17";
        $appsecret = "59531041eecf819e50830a509dbd5b19";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($ch);

        if(curl_errno($ch))
        {
            echo 'Curl error: ' . curl_error($ch);
        }
        else                                                //获取access_token成功，将数据存入KDVB
        {
            $jsoninfo     = json_decode($output, true);
            $access_token = $jsoninfo["access_token"];
            $expires_in   = $jsoninfo["expires_in"];
            $nowtime      = time();
            $access_kv    = $nowtime . " " . $expires_in . " " . $access_token ;

            $kv->set('access_token', $access_kv);
        }
        curl_close($ch);
    }
    return $access_token;
}

function sendMessage($access_token,$message)
{
    $ch = curl_init();
    curl_setopt_array( $ch,
        array(
            CURLOPT_URL=>'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token,
            CURLOPT_RETURNTRANSFER=>TRUE,
            CURLOPT_POST=>TRUE,
            CURLOPT_POSTFIELDS=>$message
        )
    );
    curl_exec($ch);
    curl_close($ch);
    return 0;
}

function msg_handing( $message )
{
    $str = explode("||",$message) ;
    $to_user = $str[0];
    $mark = $str[1];
    $to_Content = "";
    switch ($mark)
    {
    case 'Hello':
        break;
    case 'CPU':
        $temperature = $str[2];
        $used = $str[3];
        $to_Content = "CPU温度 : $temperature ℃\nCPU使用率: $used %";
        break;
    case 'Memory':
        $RAM_tatol = $str[2];
        $RAM_used  = $str[3];
        $RAM_free  = $str[4];
        $to_Content = "内存总大小:$RAM_tatol\n已用内存  :$RAM_used\n剩余内存  :$RAM_free";
        break;
    case 'Disk':
        $Disk_tatol = $str[2];
        $Disk_used  = $str[3];
        $Disk_free  = $str[4];
        $to_Content = "磁盘总大小:$Disk_tatol\n已用空间  :$Disk_used\n剩余空间  :$Disk_free";
        break;
     case 'Downloading':
        $arrlength = count($str);
        $to_Content = "正在下载:\n----------";
        for($i=2; $i<$arrlength; $i++)
        {
            $n = $i-1;
            $to_Content = $to_Content . "\n[$n]\n" . trim($str[$i]);
        }
        break;
     case 'Completed':
        $arrlength = count($str);
        $to_Content = "已完成:\n----------";
        for($i=2; $i<$arrlength; $i++)
        {
            $n = $i-1;
            $to_Content = $to_Content . "\n[$n]\n" . trim($str[$i]);
        }
        break;
     case 'Restarting':
        $to_Content = "远程下载正在重启\n请稍候。。。";
        break;
     case 'Restart':
        $to_Content = "远程下载重启完成";
        break;
     case 'Close':
        $to_Content = "远程下载已关闭";
        break;
     case 'LEDON':
        $to_Content = "LED已打开";
        break;
     case 'LEDOFF':
        $to_Content = "LED已关闭";
        break;

     case 'Playing':
        $playNow = $str[2];
        $to_Content = "正在播放:\n$playNow";
        break;
     case 'Pause2Play':
        $to_Content = "开始播放";
        break;
     case 'Play2Pause':
        $to_Content = "播放已暂停";
        break;
     case 'StoppedPlay':
        $to_Content = "播放已停止";
        break;
     case 'volumeUp':
        $to_Content = "音量已增大";
        break;
     case 'volumeDn':
        $to_Content = "音量已减小";
        break;
     case 'fullscreen':
        $to_Content = "全屏播放";
        break;
     case 'exitscreen':
        $to_Content = "退出全屏";
        break;
    }

    $access_token =get_access_token();
    //$arr = array('touser' => 'otSdrs3YmLMLxRFDm4pBZt_pjRCs', 'msgtype' => 'text', 'text' => array( 'content'=>urlencode($to_Content) ) );
    $arr = array('touser' => $to_user , 'msgtype' => 'text', 'text' => array( 'content'=>urlencode($to_Content) ) );
    $message = urldecode(json_encode($arr));
    sendMessage($access_token,$message);
    return 0;
}

?>
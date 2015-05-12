<?php

function voice_msg($contentStr)
{
    $content = "";

    $Play = array(0,"放","方","分","防","播放");
    $volumeUp = array(0,"真","大","加","高",);
    $volumeDn = array(0,"小","低","减");
    $fullscreen = array(0,"全屏","全");
    $doing = array(0,"正在","现在");
    $open = array(0,"开","开始","开启","重启");
    $close = array(0,"关","关闭","停止","退出");

    $my_array1 = array( &$Play, &$volumeUp, &$volumeDn, &$fullscreen, &$doing, &$open, &$close,);

    foreach( $my_array1 as &$temp)
    {
        foreach( $temp as $x)
        {
            if(stristr($contentStr,$x))             //判断关键字
            {
                $temp[0] = 1;
                break;
            }
        }
    }

    if( !$Play[0]  &&  !$volumeUp[0]  &&  !$volumeDn[0]  &&  !$fullscreen[0])
    {
        $light= array(0,"灯","等","照明");
        $CPU = array(0,"cpu","中央处理器");
        $RAM = array(0,"内存");
        $Disk = array(0,"盘","牌","硬盘","磁盘","存储");
        $download = array(0,"下载","下");
        $finish= array(0,"已","已经","完成");


        $my_array = array(&$light, &$CPU, &$RAM, &$Disk, &$download, &$finish);

        foreach( $my_array as &$temp)
        {
            foreach( $temp as $x)
            {
                if(stristr($contentStr,$x))             //判断关键字
                {
                    $temp[0] = 1;
                    break;
                }
            }
        }
        if( $light[0] && $open[0] )
            $content = "ONLED";
        elseif( $light[0] && $close[0] )
            $content = "OFFLED";
        elseif( $CPU[0] )
            $content = "CPU";
        elseif( $RAM[0] )
            $content = "Memory";
        elseif( $Disk[0] )
            $content = "Disk";
        elseif( $doing[0] )
            $content = "Downloading";
        elseif( $finish[0] )
            $content = "Completed";
        elseif( $open[0] && $download[0] )
            $content = "Restart";
        elseif( $close[0] && $download[0] )
            $content = "Close";

        else
            $content = "error";
        return  $content;
    }



    $Pause = array(0,"暂停");
    $List = array(0,"列表","链表");


    $my_array0 = array( &$Pause, &$List);


    foreach( $my_array0 as &$temp)
    {
        foreach( $temp as $x)
        {
            if(stristr($contentStr,$x))             //判断关键字
            {
                $temp[0] = 1;
                break;
            }
        }
    }

    if( $List[0] )
        $content = "Completed";
    elseif( $Pause[0] )
        $content = "Play2Pause";
    elseif( $open[0] )
        $content = "Pause2Play";
    elseif( $close[0] && $Play[0] )
        $content = "StopPlay";
    elseif( $volumeUp[0] )
        $content = "volumeUp";
    elseif( $volumeDn[0] )
        $content = "volumeDn";
    elseif( $fullscreen[0] && $close[0])
        $content = "exitscreen";
    elseif( $fullscreen[0] && $Play[0] )
        $content = "fullscreen";
    elseif( $doing[0] )
        $content = "Playing";
    else
    {
        $one = array(0,"一","1");
        $two = array(0,"二","2");
        $three = array(0,"三","3");
        $four = array(0,"四","4");
        $five = array(0,"五","5");
        $six = array(0,"六","6");
        $seven = array(0,"七","7");
        $eight = array(0,"八","8");
        $nine = array(0,"九","9");
        $ten = array(0,"十","10");
        $my_array00 = array( &$one, &$two, &$three, &$four, &$five, &$six,
                             &$seven, &$eight, &$nine, &$ten);

        foreach( $my_array00 as &$temp)
        {
            foreach( $temp as $x)
            {
                if(stristr($contentStr,$x))             //判断关键字
                {
                    $temp[0] = 1;
                    break;
                }
            }
        }
        if( $one[0] )
            $content = "1";
        elseif( $two[0]  )
            $content = "2";
        elseif( $three[0]  )
            $content = "3";
        elseif( $four[0]  )
            $content = "4";
        elseif( $five[0]  )
            $content = "5";
        elseif( $six[0]  )
            $content = "6";
        elseif( $seven[0]  )
            $content = "7";
        elseif( $eight[0]  )
            $content = "8";
        elseif( $nine[0]  )
            $content = "9";
        elseif( $ten[0]  )
            $content = "10";
        else
            $content = "error";
    }

    return $content;
}


?>
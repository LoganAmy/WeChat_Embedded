#! /usr/bin/env python
# -*- coding: utf-8 -*-
import urllib2
import websocket
import commands 
import time
import os
import threading

import sys

#f = file('/dev/null', 'w')
#sys.stderr = f

commands.getoutput("echo 18 > /sys/class/gpio/export")
commands.getoutput("echo out > /sys/class/gpio/gpio18/direction")
global mark_num

#Return % of CPU used and temperature
def get_CPU_info():
	cpulog_1 = commands.getoutput("cat /proc/stat | grep 'cpu ' | awk '{print $2+$3+$4+$5+$6+$7+$8\" \"$5}'")
	cpulog_1 = cpulog_1.split()
	time.sleep(1)
	cpulog_2 = commands.getoutput("cat /proc/stat | grep 'cpu ' | awk '{print $2+$3+$4+$5+$6+$7+$8\" \"$5}'")	
	cpulog_2 = cpulog_2.split()
	total = float(cpulog_2[0]) - float(cpulog_1[0])
	idle  = float(cpulog_2[1]) - float(cpulog_1[1])
	CPU_use = "%.1f" % (100 - (idle*100/total))

	res = commands.getoutput('cat /sys/class/hwmon/hwmon0/device/temp1_input')
#	res = "33333"
	CPU_temp = "%.1f" % (float(res)/1000)
	CPUinfo = "CPU||%s||%s" % (CPU_temp,CPU_use)
	return CPUinfo

#Return Memory information (unit=Mb) in a list
def get_RAM_info():
	RAMinfo = commands.getoutput("free -m | grep 'Mem' | awk '{print \"Memory||\"$2\"M||\"$3\"M||\"$4\"M\"}'")
	return	RAMinfo

#Return information about disk space as a list (unit included) 
def get_Disk_info():
	DiskSpace = commands.getoutput("df -h / | grep 'dev' | awk '{print \"Disk||\"$2\"||\"$3\"||\"$4}'")
	return	DiskSpace

#The statue of download
def download_statue(x):
	base = "/media/udisk/TDDOWNLOAD"
	Downloading = ["Downloading"]
	Completed = ["Completed"]
	cur_list = os.listdir(base)
	for item in cur_list:
		full_path = os.path.join(base, item)
		if os.path.isfile(full_path):
			if item.endswith(".td"):
				Downloading.append(os.path.splitext(item)[0])
			elif item.endswith(".cfg"):
				pass
			else:
				Completed.append(item)
	if x==0:
		return "||".join(Downloading)
	if x==1:
		global mark_num
		mark_num = len(Completed)-1
		return "||".join(Completed)

#Play Movie
def Play_Mov(num):
	listStr  = download_statue(1)
	global mark_num
	if mark_num < num:
		return "Hello"
	playList = listStr.split('||')
	playFile = os.path.join( "/media/udisk/TDDOWNLOAD", playList[num] )
	commands.getoutput("echo \"loadfile %s\" > /tmp/fifofile" % playFile )
	playInfo = "Playing||%s" % ( playList[num] )
	return playInfo

#Connect to SAE Channel
def get_url():
	while True:
		try:
			req = urllib2.Request("http://1.mylovefish.sinaapp.com/bananapi.php")
			response = urllib2.urlopen(req)
			the_page = response.read()
			url = the_page.strip()
			ws = websocket.create_connection(url)
			print url
			ws.send("Hello")
			pause_flag  = 0
			volume_flag = 60
			playInfoBak = ""
			while True:
				content = "Hello"
				str = ws.recv()
				tmp = str.split('||');
				from_user = tmp[0]
				buf = tmp[1]
				ws.send("Hello")

				if buf == "CPU":
					content = get_CPU_info()
				elif buf == "Memory":
					content = get_RAM_info()
				elif buf == "Disk":
					content = get_Disk_info()
				elif buf == "Downloading":
					content = download_statue(0)
				elif buf == "Completed":
					content = download_statue(1)
				elif buf == "Restart":
					t = threading.Thread(target=Restart,args=(ws,from_user))
					t.setDaemon(True)
					t.start()
				elif buf == "Close":
					commands.getoutput("service xunlei stop")
					content = "Close"
				elif buf == "ONLED":
					commands.getoutput("echo 1 > /sys/class/gpio/gpio18/value")
					content = "LEDON"
				elif buf == "OFFLED":
					commands.getoutput("echo 0 > /sys/class/gpio/gpio18/value")
					content = "LEDOFF"

				elif buf == "lightdm":
					commands.getoutput("ps aux |grep 'mplayer'|grep -v grep|awk '{print $2}'|xargs kill -9")
					commands.getoutput("service lightdm restart")
					content = "lightdm"
				#Play Moviei Control
				elif buf.isdigit():
					num = int(buf)
					content = Play_Mov(num)
					playInfoBak = content
					play_flag = 1
				elif buf == "Play2Pause":
					if pause_flag == 0:
						commands.getoutput("echo \"pause\" > /tmp/fifofile")
						pause_flag = 1
					content = "Play2Pause"
				elif buf == "Pause2Play":
					if pause_flag == 1:
						commands.getoutput("echo \"pause\" > /tmp/fifofile")
						pause_flag = 0
					content = "Pause2Play"
				elif buf == "StopPlay":
					commands.getoutput("echo \"stop\" > /tmp/fifofile")
					content = "StoppedPlay"
				elif buf == "volumeUp":
					volume_flag += 10 
					commands.getoutput("echo \"volume %d 1\" > /tmp/fifofile" % volume_flag )
					content = "volumeUp"
				elif buf == "volumeDn":
					volume_flag -= 10 
					commands.getoutput("echo \"volume %d 1\" > /tmp/fifofile" % volume_flag )
					content = "volumeDn"
				elif buf == "fullscreen":
					commands.getoutput("echo \"vo_fullscreen 1\" > /tmp/fifofile")
					content = "fullscreen"
				elif buf == "exitscreen":
					commands.getoutput("echo \"vo_fullscreen 0\" > /tmp/fifofile")
					content = "exitscreen"
				elif buf == "Playing":
					content = playInfoBak

				else:
					pass
				ws.send(from_user+"||"+content)
				print content
		except BaseException, e:
			get_url()


def Restart(ws,from_user):
	ws.send(from_user+"||Restarting")
	commands.getoutput("service xunlei start")
	ws.send(from_user+"||Restart")

if __name__=='__main__':

    while True:
        try:
            get_url()
        except BaseException, e:
            get_url()


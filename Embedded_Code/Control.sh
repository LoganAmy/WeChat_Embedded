#!/bin/bash
OLD_IFS=$IFS
IFS=$(echo -en "\n\b")
j=0
#playList=$(find /media/udisk/TDDOWNLOAD/ -name '*.mp4')
for k in $(find /media/udisk/TDDOWNLOAD/ -name '*.mp4')
do
	play_list[j]=$k
	j=$(expr $j + 1)
done

n=$(expr $1 - 1)
echo "loadfile ${play_list[$n]}" > /tmp/fifofile

IFS=$OLD_IFS

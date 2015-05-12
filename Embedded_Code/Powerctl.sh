!/bin/sh
if [ "$1" = "shutdown" ]; then
	shutdown -h now
elif [ "$1" = "reboot" ]; then
	reboot
elif [ "$1" = "remplayer" ]; then
	. /home/bananapi/.profile
fi

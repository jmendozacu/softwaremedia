#!/bin/sh
# location of the php binary
if [ ! "$1" = "" ] ; then
CRONSCRIPT=$1
else
CRONSCRIPT=crawler.php
fi

PHP_BIN=`which php`

# absolute path to magento installation
INSTALLDIR=`echo $0 | sed 's/crawler.sh//g'`

# prepend the installation path if not given an absolute path
if [ "$INSTALLDIR" != "" -a "`expr index $CRONSCRIPT /`" != "1" ]; then
	if ! ps auxwww | grep "$INSTALLDIR""$CRONSCRIPT" | grep -v grep 1>/dev/null 2>/dev/null ; then
		$PHP_BIN "$INSTALLDIR""$CRONSCRIPT" > /tmp/crawler.log
	fi
else
	if ! ps auxwww | grep " $CRONSCRIPT" | grep -v grep | grep -v cron.sh 1>/dev/null 2>/dev/null ; then
		$PHP_BIN $CRONSCRIPT > /tmp/crawler.log
	fi
fi

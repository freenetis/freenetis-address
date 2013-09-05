#!/bin/bash

#
# This file is part of open source system FreenetIS
# and it is released under GPLv3 licence.
# 
# More info about licence can be found:
# http://www.gnu.org/licenses/gpl-3.0.html
# 
# More info about project can be found:
# http://www.freenetis.org/
# 
#

set -e

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

# get user name
while [ true ]
do
	read -p "DB user name: " user
	
	if [ -n "$user" ]
	then
		break
	fi
done

# get password
read -sp "DB user password: " pass
echo

# get server
read -p "DB server (default localhost): " server
	
if [ -z "$server" ]
then
	server="localhost"
fi

# get port
read -p "DB port (default 3306): " port
	
if [ -z "$port" ]
then
	port="3306"
fi

# get database
read -p "DB database (default addresses): " db
	
if [ -z "$db" ]
then
	db="addresses"
fi

echo "Saving configuration"

# save configuration
echo mysql_user=$user > /etc/freenetis-addresses.ini
echo mysql_pass=$pass >> /etc/freenetis-addresses.ini
echo mysql_server=$server >> /etc/freenetis-addresses.ini
echo mysql_port=$port >> /etc/freenetis-addresses.ini
echo mysql_db=$db >> /etc/freenetis-addresses.ini

echo "Installing"

# install
mkdir /var/www/freenetis-addresses

cp ./index.php /var/www/freenetis-addresses/
cp ./import.sh /var/www/freenetis-addresses/

chmod 700 /var/www/freenetis-addresses/import.sh

# configure CRON
echo "Preparing CRON"

echo "# /etc/cron.d/freenetis-addresses: Regular CRON file for freenetis-addressses (triggered each day)" > /etc/cron.d/freenetis-addresses
echo "" >> /etc/cron.d/freenetis-addresses
echo "SHELL=/bin/bash" >> /etc/cron.d/freenetis-addresses
echo "00 5 * * *   root    /var/www/freenetis-addresses/import.sh --cron >>\"/var/log/freenetis-addresses.log\" 2>&1" >> /etc/cron.d/freenetis-addresses

if [ -x /usr/sbin/invoke-rc.d ]; then
	invoke-rc.d cron restart 3>/dev/null || true
else
	/etc/init.d/cron restart 3>/dev/null || true
fi

exit 0

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


# init enviroment variable

function style
{
	if [[ $cron == FALSE ]]
	then
		tput $1 $2
	fi
}

function print_info
{
	echo -en "== "
	style bold
	echo -n $(date '+%D %T')
	style sgr0
	echo -n " == "
	style setaf 2
	style bold
	echo $1
	style sgr0
	echo
}

function print_warning
{
	echo -en "!! "
	style bold
	echo -n $(date '+%D %T')
	style sgr0
	echo -n " !! "
	style setaf 1
	style bold
	echo $1
	style sgr0
	echo
}

function control_c
{
	echo -e "\n"
	print_warning "SIGINT? OKAY :'("
	rm -rf $directory
	exit 1
}

echo "--------------------------------------------------------------------------------"

if [[ "$1" == "--cron" ]] || [[ "$1" == "-c" ]];
then
	cron=TRUE
else
	cron=FALSE
fi

if [ -r /etc/freenetis-addresses.ini ]
then
	. /etc/freenetis-addresses.ini
else
	print_warning "Config file not found"
	exit 1
fi

# do not modify
country_id=55	# ID of Czech republic
mysql_table="addresses"
mysql_table_old=$mysql_table"_old"
mysql_table_tmp=$mysql_table"_tmp"

# get start tim
starttime=$(date '+%s')

print_info "Trying to get latest database date"

# download web page
html=$(wget -O - http://nahlizenidokn.cuzk.cz/StahniAdresniMistaRUIAN.aspx 2>/dev/null)

if [ $? != 0 ];
then
	print_warning "Cannot get latest database date"
	rm -rf $directory
	exit 1
fi

# read date from html source
datestamp=$(echo $html | sed -nre "s/.*([0-9]{8})_OB_ADR_csv\.zip.*/\1/p")

if [ $? != 0 ];
then
	print_warning "Cannot get latest database date"
	rm -rf $directory
	exit 1
fi

db_datestamp=$(mysql $mysql_db -u $mysql_user -p$mysql_pass -h $mysql_server -P $mysql_port --silent -e "SELECT value FROM config WHERE name LIKE 'datestamp'")

if [ -n "$db_datestamp" ];
then
	if [ "$db_datestamp" -ge "$datestamp" ];
	then
		print_info "Database is up to date"
		exit 0
	else
		print_warning "Database is not up to date and will be updated"
	fi
else
	print_warning "Database is empty and will be imported"
fi

# create temp directory
directory=$(mktemp -d)

# catch SIGINT
trap control_c SIGINT

# download address database
print_info "Downloading address database"

wget http://vdp.cuzk.cz/vymenny_format/csv/$datestamp\_OB_ADR_csv.zip -O $directory/addresses.zip 2>/dev/null

if [ $? != 0 ];
then
	print_warning "Cannot download addresses"
	rm -rf $directory
	exit 1
fi

# unzip
print_info "Extracting address database"

unzip -qd $directory $directory/addresses.zip

if [ $? != 0 ];
then
	print_warning "Cannot extract addresses"
	rm -rf $directory
	exit 1
fi

# prepare database for importing
print_info "Preparing address database"

FILES=$directory/CSV/*.csv
FILE_NUM=$(ls -1 $FILES | wc -l)
I=0

for f in $FILES
do
	I=$(($I+1))
	echo -en "\rPreparing $I of $FILE_NUM: $f"

	#		remove columns				#change encoding					#create number with orientation number													#create number without orientaion number							#add country id
	cat $f | cut -s -d ";" -f 3-4,7-13 | iconv -f "WINDOWS-1250" -t "UTF-8" | sed -r 's/;((č\.)(ev\.)){0,1}(č\.p\.){0,1};([0-9]*);([0-9][0-9]*);(.*)/;\3\2\5\/\6\7/g' | sed -r 's/;((č\.)(ev\.)){0,1}(č\.p\.){0,1};([0-9]*);;/;\3\2\5/g' | sed -r "s/(.*)/$country_id;\1/g" > $f.utf8
	
	if [ $? != 0 ];
	then
		echo -e "\n"
		print_warning "Cannot prepare addresses"
		rm -rf $directory
		exit 1
	fi
done

# import database
FILES=$directory/CSV/*.utf8
FILE_NUM=$(ls -1 $FILES | wc -l)
I=0

echo -e "\n"
print_info "Importing address database"

mysql $mysql_db -u $mysql_user -p$mysql_pass -h $mysql_server -P $mysql_port --silent -e "TRUNCATE TABLE $mysql_table_tmp"

if [ $? != 0 ];
then
	print_warning "Cannot clean temporary table"
	rm -rf $directory
	exit 1
fi

for f in $FILES
do
	I=$(($I+1))
	echo -en "\rImporting $I of $FILE_NUM: $f "

	mysql $mysql_db -u $mysql_user -p$mysql_pass -h $mysql_server -P $mysql_port --local-infile -e "LOAD DATA LOCAL INFILE '$f' INTO TABLE $mysql_table_tmp FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n' IGNORE 1 LINES"
	
	if [ $? != 0 ];
	then
		echo -e "\n"
		print_warning "Cannot import addresses"
		rm -rf $directory
		exit 1
	fi
done

echo -e "\n"
print_info "Updating database"

mysql $mysql_db -u $mysql_user -p$mysql_pass -h $mysql_server -P $mysql_port --silent -e "RENAME TABLE $mysql_table TO $mysql_table_old; RENAME TABLE $mysql_table_tmp TO $mysql_table; RENAME TABLE $mysql_table_old TO $mysql_table_tmp; REPLACE INTO config VALUES ('datestamp', '$datestamp'); TRUNCATE TABLE $mysql_table_tmp"

if [ $? != 0 ];
then
	print_warning "Cannot update database"
	rm -rf $directory
	exit 1
fi

# clean up
rm -rf $directory

# count script run time
endtime=$(date '+%s')
seconds=$(( $endtime - $starttime ))

hours=$(($seconds / 3600))
seconds=$(($seconds % 3600))
minutes=$(($seconds / 60))
seconds=$(($seconds % 60))

print_info "Import takes $(printf '%d:%02d:%02d\n' $hours $minutes $seconds)"

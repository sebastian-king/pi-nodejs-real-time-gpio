#!/bin/bash

#create and go to web root
mkdir -p /var/www/h/
cd /var/www/h/

#get package files and put them into the web root (github?)
git clone https://github.com/sebastian-king/pi-nodejs-real-time-gpio .

cd assets/
source "$(dirname "$0")/config.sh"
#./install.sh

echo "alias pslw='ps f -wwweopid,user,etime,args'" >> ~/.bashrc
#echo "alias lso=\"ls -alG | awk '{k=0;for(i=0;i<=8;i++)k+=((substr(\$1,i+2,1)~/[rwx]/)*2^(8-i));if(k)printf(\\\" %0o \\\",k);print}'\"" >> ~/.bashrc # doesn't format correctly when printed

#install acme script for SSL
wget -O -  https://get.acme.sh | sh # this will install it and save the credentials below for future use
export Namecom_Username="${namecom_username}"
export Namecom_Token="${namecom_token}"
/root/.acme.sh/acme.sh --issue --dns dns_namecom -d "${domain}"
ln -s "/root/.acme.sh/${domain}/fullchain.cer" "assets/ssl/fullchain.cer"
ln -s "/root/.acme.sh/${domain}/${domain}.key" "assets/ssl/privkey.key"

#install programs
apt-get install apache2 php pigpio nodejs nodejs-legacy npm runit

#make sure we install npm modules into the correct directory
cd /var/www/h/npm/

#install npm modules
npm install pigpio websocket

#add crontab for runit
echo "@reboot	runsvdir /etc/service/" > /tmp/cron
echo "0 0 15 * *	acme.sh --issue --dns dns_namecom -d '${domain}'" >> /tmp/cron
crontab /tmp/cron
rm /tmp/cron
crontab -l # just to check and make sure they got added

#symlink
ln -s /var/www/h/assets/runit/cool /etc/sv/
ln -s /etc/sv/cool /etc/service/

ln -s /var/www/h/assets/runit/wss /etc/sv/
ln -s /etc/sv/wss /etc/service/

#load apache
ln -s /var/www/h/assets/apache/h.conf /etc/apache2/sites-available/h.conf
ln -s /var/www/h/assets/apache/h-ssl.conf /etc/apache2/sites-available/h-ssl.conf

a2ensite h.conf h-ssl.conf
a2enmod ssl rewrite
service apache2 restart

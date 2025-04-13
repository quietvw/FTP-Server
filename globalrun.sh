#!/bin/bash
chmod -R 777 /nebulaftp
# Run FTP Server
mkdir /nebulaftp/root
#cd /nebulaftp/ && python3 main.py &

# Run Web Server
php -S 0.0.0.0:80 -t /nebulaftp/webroot

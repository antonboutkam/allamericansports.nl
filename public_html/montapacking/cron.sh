#!/bin/sh
php -- $(dirname $0)/sales_order.php -f
php -- $(dirname $0)/upload_files.php
php -- $(dirname $0)/article_info.php -f
php -- $(dirname $0)/upload_files.php
php -- $(dirname $0)/download_files.php
php -- $(dirname $0)/process_pulled_files.php
php -- $(dirname $0)/cleanup_files.php

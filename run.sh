#!/bin/bash
php -S localhost:3000 $(dirname $(realpath $0))/router.php

#!/usr/bin/env bash

PATH_DIR_CUR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

if [ -z "$1" ]; then
    echo "Usage: spc <platform>"
    exit 1
elif [ "$1" != "amd64" ] && [ "$1" != "arm64" ]; then
    echo "Invalid platform: $1"
    exit 1
fi

docker run --rm -it --name="php-spc-$1" --platform "linux/$1" \
-v "dev-common:/dev-data" \
-v "/Users/aybarsm/PersonalSync/Coding/php:/work" \
--env-file "${PATH_DIR_CUR}/spc.env" \
aybarsm/php:spc

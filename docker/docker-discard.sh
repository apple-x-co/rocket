#!/usr/bin/env bash

cd "$(dirname "$0")" || exit

count=$(docker compose ps -q | awk 'END{print NR}')

if [ "$count" == "0" ]; then
    echo "Docker is already discarded !!"
else
    docker compose down -v
fi

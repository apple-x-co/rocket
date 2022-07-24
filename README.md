# rocket

[![Coding Standards](https://github.com/apple-x-co/rocket/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/apple-x-co/rocket/actions/workflows/coding-standards.yml)
[![Static Analysis](https://github.com/apple-x-co/rocket/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/apple-x-co/rocket/actions/workflows/static-analysis.yml)

## Requirement

PHP version 5.4.0 or greater.

## Installation

```bash
# Download using wget
wget https://github.com/apple-x-co/rocket/releases/latest/download/rocket.phar

# Then test the downloaded PHARs
chmod u+x rocket.phar
./rocket.phar --init > ./rocket.json
```

## Usage

```bash
# show information
./rocket.phar --info #--no-color

# slack notification test
./rocket.phar --config ./rocket.json --notify-test

# verify rocket.json
./rocket.phar --config ./rocket.json --verify

# dry run (default)
./rocket.phar --config ./rocket.json --sync dry

# git pull & sync confirm
./rocket.phar --config ./rocket.json --git pull --sync confirm

# git pull & sync force (crontab)
./rocket.phar --config ./rocket.json --git pull --sync force

# not git
./rocket.phar --config ./rocket.json --sync confirm

# notify
cat example.txt | ./rocket.phar --config ./rocket.json --notify
```

## Update

```bash
./rocket.phar --upgrade
```

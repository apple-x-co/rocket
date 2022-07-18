# rocket

## Requirement

* PHP 5.4+

## Installation

```bash
wget https://github.com/apple-x-co/rocket/releases/latest/download/rocket.phar
chmod u+x rocket.phar
./rocket.phar --init > ./rocket.json
```

## Information

```bash
./rocket.phar --info #--no-color
```

## Update

```bash
./rocket.phar --upgrade
```

## Usage

```bash
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

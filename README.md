# rocket

## Requirement

* PHP 5.4+

## Installation

```bash
wget https://github.com/apple-x-co/rocket/releases/download/0.1.7/rocket.phar
chmod u+x rocket.phar
./rocket.phar --init > ./rocket.json
```

```bash
# slack notification test
./rocket.phar --config ./rocket.json --notify-test

# verify rocket.json
./rocket.phar --config ./rocket.json --verify
```

## Update

```bash
./rocket.phar --upgrade
```

## Usage

```bash
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

## Build phar file

```bash
composer run-script build
```

※プロジェクト内全てのファイルが圧縮されます。クレデンシャルファイルは配置しないこと。

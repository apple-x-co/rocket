# rocket

## Requirement

* PHP 5.6+

## Installation

```bash
wget https://github.com/apple-x-co/rocket/releases/download/0.1.7/rocket.phar
chmod u+x rocket.phar
./rocket.phar --init > ./rocket.json
```

```bash
# notification test
./rocket.phar --rocket ./rocket.json --notify-test

# verify rocket.json
./rocket.phar --rocket ./rocket.json --verify
```

## Update

```bash
./rocket.phar --upgrade
```

## Usage

```bash
# dry run (default)
./rocket.phar --rocket ./rocket.json --sync dry

# git pull & sync confirm
./rocket.phar --rocket ./rocket.json --git pull --sync confirm

# git pull & sync force (crontab)
./rocket.phar --rocket ./rocket.json --git pull --sync force

# not git
./rocket.phar --rocket ./rocket.json --sync confirm
```

## Build phar file

```bash
composer run-script build
```

※プロジェクト内全てのファイルが圧縮されます。クレデンシャルファイルは配置しないこと。

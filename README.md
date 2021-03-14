# rocket

## How to

```bash
# print rocket.json
php ./rocket.phar --init > ./rocket.json
```

```bash
# notification test
php ./rocket.phar --rocket ./rocket.json --notify-test

# verify rocket.json
php ./rocket.phar --rocket ./rocket.json --verify
```

```bash
# dry run (default)
php ./rocket.phar --rocket ./rocket.json --sync dry

# git pull & sync confirm
php ./rocket.phar --rocket ./rocket.json --git pull --sync confirm

# git pull & sync force (crontab)
php ./rocket.phar --rocket ./rocket.json --git pull --sync force

# not git
php ./rocket.phar --rocket ./rocket.json --sync confirm
```

```bash
# upgrade
php ./rocket.phar --upgrade

# upgrade (no ZipArchive)
php ./rocket.phar --upgrade --unzip /usr/bin/unzip
```

## Development

### Build

```bash
composer run-script build
```

※プロジェクト内全てのファイルが圧縮されます。クレデンシャルファイルは配置しないこと。

# rocket

## Requirement

* PHP version 5.4.0 or greater.

## Installation

```bash
# Download using wget
wget https://github.com/apple-x-co/rocket/releases/latest/download/rocket.phar

# Then test the downloaded phars
chmod u+x rocket.phar
./rocket.phar --init > ./rocket.json
```

## Usage

### Show information

```bash
./rocket.phar --info #--no-color
```

### Verification configure file

```bash
./rocket.phar --config ./rocket.json --verify
```

### Slack notification test

```bash
./rocket.phar --config ./rocket.json --notify-test
```

### Dry run sync directory

```bash
./rocket.phar --config ./rocket.json --sync dry
```

### Git pull & Confirm sync directory

```bash
./rocket.phar --config ./rocket.json --git pull --sync confirm
```

### Git pull & Force sync directory

```bash
./rocket.phar --config ./rocket.json --git pull --sync force
```

### Sync directory only

```bash
./rocket.phar --config ./rocket.json --sync confirm
```

### Slack notification

```bash
echo "HELLO WORLD" | ./rocket.phar --config ./rocket.json --notify
```

### Download latest version rocket

```bash
./rocket.phar --upgrade
```

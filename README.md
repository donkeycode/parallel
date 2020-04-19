# Run php commands in parallel

[![Build Status](https://travis-ci.org/donkeycode/parallel.svg?branch=master)](https://travis-ci.org/donkeycode/parallel)

## Installation 

``` bash
URL=$(curl --silent "https://api.github.com/repos/donkeycode/parallel/releases/latest"  | grep browser_download_url | sed -E 's/.*"([^"]+)".*/\1/')
wget $URL
chmod +x parallel.phar
```

Optionaly can be moved to `/usr/local/bin`

``` bash
mv parallel.phar /usr/local/bin/parallel
```

Like that you can use everywhere `cat commands.txt | parallel`

## Basic usage

``` bash
cat commands.txt | php parallel.phar
```

Limit threads (default 10)

``` bash
cat commands.txt | php parallel.phar --threads=20
cat commands.txt | php parallel.phar -t20
```

Inline commands

``` bash
php parallel.phar "command1" "command2" "command3"
```
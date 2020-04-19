# Run php commands in parallel

Basic usage

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
# pzem-php
Library to get parameters of PZEM-014 powermeters

Requirements:
1. Just PHP with cli;
2. PHP-DIO library.

To install php-dio, you can use: `pecl install dio`

That's all. Enjoy.

```php
./main.php
Array
(
    [voltage] => 229
    [current] => 12.423
    [power] => 2795.7
    [energy] => 38261
    [frequency] => 50
    [pf] => 0.98
    [alarm] => OK
)
```

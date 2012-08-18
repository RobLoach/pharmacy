<?php

/*
 * This file is part of Pharmacy.
 *
 * (c) Rob Loach <robloach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

$base = dirname(__DIR__);
$top = dirname(dirname($base));
if ((!$loader = includeIfExists($base.'/vendor/autoload.php')) && (!$loader = includeIfExists($top.'/autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

return $loader;

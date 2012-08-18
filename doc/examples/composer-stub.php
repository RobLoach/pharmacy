<?php
global $version;
$stub = <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

Phar::mapPhar('composer.phar');

EOF;

// add warning once the phar is older than 30 days
if (preg_match('{^[a-f0-9]+$}', $version)) {
    $warningTime = time() + 30*86400;
    $stub .= "define('COMPOSER_DEV_WARNING_TIME', $warningTime);\n";
}

return $stub . <<<'EOF'
require 'phar://composer.phar/bin/composer';

__HALT_COMPILER();
EOF;

<?php

$stub = <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of Pharmacy.
 *
 * (c) Rob Loach <robloach@gmail.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

Phar::mapPhar('pharmacy.phar');
require 'phar://pharmacy.phar/bin/pharmacy';
__HALT_COMPILER();
EOF;
return $stub;

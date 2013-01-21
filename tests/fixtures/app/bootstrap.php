<?php

/*
 * This file is part of the BootstrapBundle package.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$vendor = realpath(__DIR__.'/../../../vendor');

$loader = require $vendor.'/autoload.php';
$loader->add('Demo',realpath(__DIR__.'/../src'));

use Doctrine\Common\Annotations\AnnotationRegistry;
// intl
if (!function_exists('intl_get_error_code')) {
    require_once $vendor.'/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', $vendor.'/vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
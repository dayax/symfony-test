<?php

namespace dayax\symfony\test\tests\fixtures\app;
require_once __DIR__.'/bootstrap.php';
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),            
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Demo\DemoBundle(),
        );
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/default.yml');
    }
    
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/'.'symfony-test/cache';
    }
    
    public function getLogDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/'.'symfony-test/log';
    }
}

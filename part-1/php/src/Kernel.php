<?php

namespace App;

use Bref\SymfonyBridge\BrefKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
//use Symfony\Component\HttpKernel\Kernel as BaseKernel;

//class Kernel extends BaseKernel
class Kernel extends BrefKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
            return '/tmp/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
            return '/tmp/log/';
    }
}

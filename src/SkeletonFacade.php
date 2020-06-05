<?php

namespace Digikraaft\Skeleton;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Digikraaft\Skeleton\Skeleton
 */
class SkeletonFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'skeleton';
    }
}

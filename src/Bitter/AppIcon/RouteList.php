<?php

/**
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.1.1
 */

namespace Bitter\AppIcon;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\AppIcon\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/app_icon')
            ->routes('dialogs/support.php', 'app_icon');
    }
}
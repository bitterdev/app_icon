<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.1.1
 */

namespace Bitter\AppIcon;

use Concrete\Core\Http\Response;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Concrete\Core\Support\Facade\Application;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\AppIcon\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/app_icon')
            ->routes('dialogs/support.php', 'app_icon');

        $app = Application::getFacadeApplication();
        /** @var $responseFactory ResponseFactory */
        $responseFactory = $app->make(ResponseFactory::class);
        /** @var PackageService $packageService */
        $packageService = $app->make(PackageService::class);
        $packageEntity = $packageService->getByHandle("app_icon");
        /** @var Package $pkg */
        $pkg = $packageEntity->getController();

        /** @noinspection PhpDeprecationInspection */
        $router->register("/bitter/app_icon/reminder/hide", function () use ($app, $responseFactory, $pkg) {
            $pkg->getConfig()->save('reminder.hide', true);
            $responseFactory->create("", Response::HTTP_OK)->send();
            $app->shutdown();
        });

        /** @noinspection PhpDeprecationInspection */
        $router->register("/bitter/app_icon/did_you_know/hide", function () use ($app, $responseFactory, $pkg) {
            $pkg->getConfig()->save('did_you_know.hide', true);
            $responseFactory->create("", Response::HTTP_OK)->send();
            $app->shutdown();
        });

        /** @noinspection PhpDeprecationInspection */
        $router->register("/bitter/app_icon/license_check/hide", function () use ($app, $responseFactory, $pkg) {
            $pkg->getConfig()->save('license_check.hide', true);
            $responseFactory->create("", Response::HTTP_OK)->send();
            $app->shutdown();
        });
    }
}
<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\AppIcon;

use Bitter\AppIcon\Provider\ServiceProvider;
use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $pkgHandle = 'app_icon';
    protected $pkgVersion = '2.7.2';
    protected $appVersionRequired = '9.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/AppIcon' => 'Bitter\AppIcon',
    ];

    public function getPackageDescription()
    {
        return t('Automatically create app icons and include them in your HTML code.');
    }

    public function getPackageName()
    {
        return t('App Icon');
    }

    public function on_start()
    {
        /** @var ServiceProvider $serviceProvider */
        $serviceProvider = $this->app->make(ServiceProvider::class);
        $serviceProvider->register();
    }

    public function install()
    {
        $pkg = parent::install();
        $this->installContentFile("install.xml");
        return $pkg;
    }

}

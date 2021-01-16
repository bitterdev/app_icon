<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\AppIcon\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Page\Controller\DashboardPageController;
use Bitter\AppIcon\Settings;

/** @noinspection PhpUnused */

class AppIcon extends DashboardPageController
{
    public function view()
    {
        /** @var Settings $settings */
        $settings = $this->app->make(Settings::class);

        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->request->getMethod() === "POST") {
            $settings->setAppIconFileId($this->post("appIcon"));
        }

        $this->set("appIcon", $settings->getAppIconFile());
    }
}

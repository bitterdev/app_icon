<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\AppIcon\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Page\Controller\DashboardSitePageController;

/** @noinspection PhpUnused */

class AppIcon extends DashboardSitePageController
{
    public function view()
    {
        $config = $this->getSite()->getConfigRepository();

        if ($this->request->getMethod() === "POST") {
            $config->save("settings.app_icon_file_id", (int)$this->request->request->get("appIcon", 0));
        }

        $this->set("appIcon", $config->get("settings.app_icon_file_id"));
    }
}

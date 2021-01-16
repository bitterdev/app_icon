<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.1.1
 */

namespace Bitter\AppIcon;

use Concrete\Core\Application\Application;
use Concrete\Core\Cache\Level\ExpensiveCache;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\File\File;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page;

class Settings
{
    protected $app;
    protected $packageEntity;
    /** @var Package */
    protected $package;
    protected $packageService;
    protected $cache;

    public function __construct(
        Application $application,
        PackageService $packageService,
        ExpensiveCache $cache
    )
    {
        $this->app = $application;
        $this->packageService = $packageService;
        $this->packageEntity = $this->packageService->getByHandle('app_icon');
        $this->package = $this->packageEntity->getController();
        $this->cache = $cache;
    }


    private function getSetting($keyName, $defaultValue)
    {
        return $this->package->getConfig()->get($keyName, $defaultValue);
    }

    private function setSetting($keyName, $value)
    {
        return $this->package->getConfig()->save($keyName, $value);
    }

    public function getAppIconFile()
    {
        return File::getById($this->getAppIconFileId());
    }

    public function hasAppIconFile()
    {
        return $this->getAppIconFile() instanceof FileEntity;
    }

    public function getAppIconFileId()
    {
        $fileId = (int)$this->getSetting("settings.app_icon_file_id", 0);

        /*
         * Check for page specific app icon
         */

        $page = Page::getCurrentPage();

        if ($page instanceof Page) {
            /** @var $pageSpecificAppIconFile FileEntity */
            $pageSpecificAppIconFile = $page->getAttribute("app_icon");

            if ($pageSpecificAppIconFile instanceof FileEntity) {
                $fileId = $pageSpecificAppIconFile->getFileID();
            }
        }

        return $fileId;
    }

    public function setAppIconFileId($fileId)
    {
        $cacheItem = $this->cache->getItem('bitter.app_icon.icon_html');
        $cacheItem->clear();

        return $this->setSetting("settings.app_icon_file_id", (int)$fileId);
    }
}

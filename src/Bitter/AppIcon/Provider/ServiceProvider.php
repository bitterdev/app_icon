<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.1.1
 */

namespace Bitter\AppIcon\Provider;

use Concrete\Core\Cache\Level\ExpensiveCache;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Foundation\Service\Provider;
use Bitter\AppIcon\RouteList;
use /** @noinspection PhpDeprecationInspection */
    Concrete\Core\Legacy\ImageHelper;
use Concrete\Core\Page\Event;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Router;
use Concrete\Core\Site\Service as SiteService;
use Concrete\Core\View\View;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Exception;
use stdClass;

class ServiceProvider extends Provider
{

    public function register()
    {
        $this->disableConcreteBookmarkIcons();
        $this->injectToHeader();
        $this->initializeRoutes();
    }

    private function initializeRoutes()
    {
        /** @var Router $router */
        $router = $this->app->make("router");
        $list = new RouteList();
        $list->loadRoutes($router);
    }

    private function getIconFile(): ?File
    {
        /** @var SiteService $siteService */
        $siteService = $this->app->make(SiteService::class);
        $site = $siteService->getSite();
        $config = $site->getConfigRepository();

        return \Concrete\Core\File\File::getByID($config->get("settings.app_icon_file_id"));
    }

    private function getResizedAppIconUrl($width, $height)
    {
        $resizedAppIconUrl = '';

        $file = $this->getIconFile();

        if ($file instanceof File) {
            try {
                /** @var ImageHelper $imageHelper */
                /** @noinspection PhpDeprecationInspection */
                $imageHelper = $this->app->make(ImageHelper::class);
                $generatedImage = $imageHelper->getThumbnail($file, $width, $height, true);

                if ($generatedImage instanceof stdClass) {
                    $resizedAppIconUrl = $generatedImage->src;
                }

            } catch (Exception $err) {
                // Skip issue
            }
        }

        return $resizedAppIconUrl;
    }

    private function getRealResizedAppIconUrl()
    {
        $retVal = null;

        $file = $this->getIconFile();
        if ($file instanceof File) {
            $filesystem = $file->getFileStorageLocationObject()->getFileSystemObject();

            $configuration = $file->getFileStorageLocationObject()->getConfigurationObject();

            $fID = $file->getFileID();

            $fileVersion = $file->getApprovedVersion();

            if ($fileVersion instanceof Version) {
                try {
                    $timestamp = $fileVersion->getFileResource()->getTimestamp();
                    $icoFileName = '/cache/thumbnails/' . md5(implode(':', [$fID, $timestamp])) . ".ico";

                    if ($filesystem->has($icoFileName) === false) {
                        $fileData = $fileVersion->getFileContents();

                        $imagine = new Imagine();

                        $resizedImage = $imagine->load($fileData)->resize(new Box(16, 16))->crop(new Point(0, 0), new Box(16, 16));

                        if ($resizedImage instanceof Image) {
                            // https://msdn.microsoft.com/de-de/library/windows/desktop/dd183376(v=vs.85).aspx
                            $bitmapData = pack("VVVvvVVVVVV", 40, 16, 32, 1, 32, 0, 0, 0, 0, 0, 0);

                            for ($y = 15; $y >= 0; $y--) {
                                for ($x = 0; $x < 16; $x++) {
                                    /** @noinspection PhpComposerExtensionStubsInspection */
                                    $bitmapData .= pack('V', imagecolorat($resizedImage->getGdResource(), $x, $y) & 0xFFFFFF);
                                }
                            }

                            // https://msdn.microsoft.com/en-us/library/ms997538.aspx
                            $icoHeader = pack("vvvCCCCvvVV", 0, 1, 1, 16, 16, 0, 0, 1, 32, strlen($bitmapData), 22);

                            // Merge ico header + bitmap data together
                            $icoFileContent = $icoHeader . $bitmapData;

                            // write to file
                            try {
                                $filesystem->write($icoFileName, $icoFileContent);
                            } catch (FileExistsException $e) {
                                // Skip issue
                            }
                        }
                    }

                    $retVal = $configuration->getPublicURLToFile($icoFileName);
                } catch (FileNotFoundException $e) {
                    // Skip issue
                }
            }
        }


        return $retVal;
    }

    private function getMimeType()
    {
        $file = $this->getIconFile();

        if ($file instanceof File) {
            $fileVersion = $file->getApprovedVersion();

            if ($fileVersion instanceof Version) {
                switch (strtolower($fileVersion->getExtension())) {
                    case "png":
                        return "image/png";

                    case "gif":
                        return "image/gif";

                    case "jpg":
                    case "jpeg":
                    default:
                        return "image/jpeg";
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    private function disableConcreteBookmarkIcons()
    {
        /** @var SiteService $siteService */
        $siteService = $this->app->make(SiteService::class);
        $config = $siteService->getSite()->getConfigRepository();
        $config->set('misc.favicon_fid', 0);
        $config->set('misc.app_icon_fid', 0);
        $config->set('misc.iphone_home_screen_thumbnail_fid', 0);
        $config->set('misc.android_home_screen_thumbnail_fid', 0);
        $config->set('misc.modern_tile_thumbnail_fid', 0);
    }

    private function generateIconHtml()
    {
        $iconHtml = "";

        // Favicons
        /** @noinspection HtmlUnknownTarget */
        $iconHtml .= sprintf("<link rel=\"shortcut icon\" href=\"%s\" type=\"image/x-icon\"/>", $this->getRealResizedAppIconUrl());
        /** @noinspection HtmlUnknownTarget */
        $iconHtml .= sprintf("\n<link rel=\"icon\" href=\"%s\" type=\"image/x-icon\"/>", $this->getRealResizedAppIconUrl());

        // iOS icons
        $appleIconSizes = [57, 60, 72, 76, 114, 120, 144, 152, 180];

        foreach ($appleIconSizes as $size) {
            /** @noinspection HtmlUnknownTarget */
            $iconHtml .= sprintf(
                "\n<link rel=\"apple-touch-icon\" sizes=\"%sx%s\" href=\"%s\">",
                $size,
                $size,
                $this->getResizedAppIconUrl($size, $size)
            );
        }

        // Android icons
        $androidIconSizes = [32, 96, 128, 196];

        foreach ($androidIconSizes as $size) {
            /** @noinspection HtmlUnknownTarget */
            $iconHtml .= sprintf(
                "\n<link rel=\"icon\" type=\"image/%s\" sizes=\"%sx%s\"  href=\"%s\">",
                $this->getMimeType(),
                $size,
                $size,
                $this->getResizedAppIconUrl($size, $size)
            );
        }

        // Windows icons
        $windowsIconSizes = [
            "msapplication-TileImage" => 144,
            "msapplication-square70x70logo" => 70,
            "msapplication-square150x150logo" => 150,
            "msapplication-square310x310logo" => 310
        ];

        foreach ($windowsIconSizes as $iconName => $size) {
            $iconHtml .= sprintf(
                "\n<meta name=\"%s\" content=\"%s\" />",
                $iconName,
                $this->getResizedAppIconUrl($size, $size)
            );
        }

        return $iconHtml;
    }

    private function getIconHtml()
    {
        /** @var $cache ExpensiveCache */
        $cache = $this->app->make(ExpensiveCache::class);

        /** @var SiteService $siteService */
        $siteService = $this->app->make(SiteService::class);
        $site = $siteService->getSite();
        $siteId = $site->getSiteID();

        $cacheItem = $cache->getItem('bitter.app_icon.icon_html_' . $siteId);

        if ($cacheItem->isMiss()) {
            $cacheItem->lock();
            $iconHtml = $this->generateIconHtml();
            $cache->save($cacheItem->set($iconHtml)->expiresAfter(2592000));
        } else {
            $iconHtml = $cacheItem->get();
        }

        return $iconHtml;
    }


    private function injectToHeader()
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->app->make(EventDispatcherInterface::class);
        $eventDispatcher->addListener("on_page_view", function ($pageEvent) {
            /** @var Event $pageEvent */
            $page = $pageEvent->getPageObject();

            if ($page instanceof Page) {
                if ($this->getIconFile() instanceof File) {
                    View::getInstance()->addHeaderItem($this->getIconHtml());
                }
            }
        });
    }
}
<?php

namespace M2Boilerplate\CriticalCss\Service;

use Magento\Framework\View\Url\CssResolver;
use Magento\Store\Model\StoreManager;

class CssProcessor
{
    /** @var CssResolver */
    protected $cssResolver;

    /** @var StoreManager */
    protected $storeManager;

    public function __construct(
        CssResolver $cssResolver,
        StoreManager $storeManager
    )
    {
        $this->cssResolver = $cssResolver;
        $this->storeManager = $storeManager;
    }

    public function process(string $cssContent)
    {
        $pattern = '@(\.\./)*(/pub)*/(static.*)@i'; // matches paths that contain pub/static/ or just static/
        $css = $this->cssResolver->replaceRelativeUrls($cssContent, function ($path) use ($pattern) {
            $matches = [];
            if(preg_match($pattern, $path, $matches[0])) {
                /**
                 * ../../../../../../pub/static/version/frontend/XXX/YYY/de_DE/ZZZ/asset.ext
                 * becomes
                 * /pub/static/version/frontend/XXX/YYY/de_DE/ZZZ/asset.ext
                 */
                return $matches[0][0];
            }
            return $path;
        });
        return $css;
    }

}

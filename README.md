# M2Boilerplate Critical CSS

Magento 2 module to automatically generate critical css with [critical](https://github.com/addyosmani/critical)

## Features:
* generate critical css with a simple command
* Fallback critical css for "empty" pages
* Add urls by creating a custom provider  

## Installation

Install the critical binary. Install instructions can be found on the [critical website](https://github.com/addyosmani/critical#install). Only versions >=2.0.3 are supported.

Add this extension to your Magento installation with Composer:

    composer require m2-boilerplate/module-critical-css

## Usage

### Configuration

The critical css feature needs to be enabled (available in 2.3.4+): 

    bin/magento config:set dev/css/use_css_critical_path 1

Features can be customised in *Stores > Configuration > Developer > CSS*.

### Generate critical css

Run the following command

    bin/magento m2bp:critical-css:generate
    
Afterwards you should find the the generated css in ``var/critical-css``. The css will now be integrated automatically on your website. 

## Add additional URLs via a custom provider

The following example adds the magento contact page via a custom provider:

```php
<?php

namespace Vendor\Module\Provider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use M2Boilerplate\CriticalCss\Provider\ProviderInterface;
  
class CustomProvider implements ProviderInterface
{
    const NAME = 'custom_example';

    /**
     * @var UrlInterface
     */
    protected $url;

    public function __construct(UrlInterface $url)
    {
        $this->url = $url;
    }


    public function getUrls(StoreInterface $store): array
    {
        return [
            'contact_index_index' => $this->url->getUrl('contact'),
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1200;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if ($request->getModuleName() !== 'contact' || !$request instanceof Http) {
            return null;
        }
        
        if ($request->getFullActionName('_') === 'contact_index_index') {
            return 'contact_index_index';
        }

        return null;
    }
}
```

Add the new provider via DI: 

in your module´s etc/di.xml add the following: 

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="M2Boilerplate\CriticalCss\Provider\Container">
        <arguments>
            <argument name="providers" xsi:type="array">
                <item name="custom_example" xsi:type="object">Vendor\Module\Provider\CustomProvider</item>
            </argument>
        </arguments>
    </type>
</config>
```

## License
See the [LICENSE](LICENSE) file for license info (it's the MIT license).

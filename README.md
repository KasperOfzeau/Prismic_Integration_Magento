# Prismic integration module

## Description
API connection between Prismic and Magento

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Prismic ViewModel](#prismic-viewmodel)
- [Contributing](#contributing)

## Installation
Install Elgentos module 
```
composer require elgentos/module-prismicio
bin/magento s:up
```
To install the dependencies, run the following command:
```
npm install
```
Enable the module in Magento:
```
bin/magento module:enable PrismicIntegration
bin/magento setup:upgrade
```
## Configuration 
Go in Magento to Stores->Configuration->Elgentos

<img src="https://user-images.githubusercontent.com/431360/100359099-60a84480-2ff7-11eb-87e2-4a01ec82fdbc.png"> 

## Prismic ViewModel

This file is responsible for fetching and rendering content slices from the Prismic CMS within a Magento storefront.

### Namespaces and Dependencies

The file starts by declaring its namespace and importing necessary classes:

```php
namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\LayoutInterface;
use Marleen\PrismicIntegration\Model\Slices\{ButtonSliceInterface, CtaSliceInterface, HeroSliceInterface, ReviewsSliceInterface, StepsSliceInterface, TitleWithTextSliceInterface, UspsSliceInterface};
```

### Properties

The class has two protected properties:

```php
protected HttpRequest $http;
protected LayoutInterface $layout;
```

These are used to handle HTTP requests and layout rendering in Magento.

### Methods

#### `getSlug`

This method retrieves the last part of the current URL, which can be used as a slug for identifying content:

```php
public function getSlug(): string
{
    $currentUrl = $this->http->getServer('REQUEST_URI');
    $parts = explode('/', trim($currentUrl, '/'));
    return end($parts);
}
```

#### `displaySlices`

This method renders HTML for provided slices using their respective Magento blocks. It loops through the slices and checks if each slice type has a corresponding block configuration:

```php
// Render HTML for provided slices using their respective Magento blocks
    public function displaySlices(array $slices, mixed $block): string
    {
        $htmlOutput = '';
        $sliceMap = $this->getSliceMap();

        foreach ($slices as $slice) {
            $type = $slice->slice_type;
            if (!array_key_exists($type, $sliceMap)) {
                continue;
            }

            $config = $sliceMap[$type];
            $childBlock = $block->getChildBlock($config['blockName']);
            if (!$childBlock) {
                continue;
            }

            $attributes = $config['attributes'];

            // Sets attributes to variation when available
            if ($slice->variation !== 'default') {
                if ($type === 'hero') {
                    if ($slice->variation === "heroWithButton") {
                        $attributes = $config['heroWithButton']['attributes'];
                    }
                }
            }

            $this->setSliceAttributes($slice, $childBlock, $attributes); // Set attributes to block
            $this->setVariation($slice->variation, $childBlock);  // Set the variation name to block
            $htmlOutput .= $childBlock->toHtml();
        }
        return $htmlOutput;
    }
```

#### `getSliceMap`

This protected method maps slice types to their corresponding block names and attributes required for rendering:

```php
protected function getSliceMap(): array
{
    return [
         HeroSliceInterface::TYPE => [
            'blockName' => HeroSliceInterface::BLOCK_NAME,
            'attributes' => HeroSliceInterface::DEFAULT,
            'heroWithButton' => [
                'attributes' => HeroSliceInterface::HEROWITHBUTTON,
            ]
        ],
        ButtonSliceInterface::TYPE => [
            'blockName' => ButtonSliceInterface::BLOCK_NAME,
            'attributes' => ButtonSliceInterface::ATTRIBUTES
        ],
        // Other slice types...
    ];
}
```

#### `setSliceAttributes`

This protected method sets attributes on child blocks based on the slice's primary attributes. It handles special cases like reviews and other list attributes:

```php
protected function setSliceAttributes(object $slice, mixed $childBlock, array $attributes): void
{
    $sliceBlock = $this->layout->createBlock(\Magento\Framework\View\Element\Template::class);
    foreach ($attributes as $attribute) {
        $value = $slice->primary->{$attribute} ?? '';
        if ($attribute === 'reviews') {
            $value = array_map(fn($item) => $item->review->id, $slice->items);
        } elseif (in_array($attribute, ['steps', 'texts', 'usps'])) {
            $value = $slice->items;
        }

        $attribute = $this->camelCaseToSnakeCase($attribute);
        $sliceBlock->setData($attribute, $value);
    }
    $childBlock->setData('slice', $sliceBlock);
}
```
#### `setVariation`

Set the variation name of a block based on the slice's variation property

```php
protected function setVariation(string $variation, mixed $childBlock): void
{
    $childBlock->setData('blockVariation', $variation);
}
```

### Summary

- **Purpose**: The `Prismic` ViewModel is used to integrate Prismic content into a Magento storefront.
- **Key Methods**: 
  - `getSlug()` retrieves the URL slug.
  - `displaySlices()` renders slices using Magento blocks.
  - `getSliceMap()` maps slice types to their blocks and attributes.
  - `setSliceAttributes()` sets attributes on child blocks based on slice data.
  - `setVariation()` set the variation name of a block based on the slice's variation property

## Contributing
[Kasper Beljaars](https://github.com/KasperOfzeau)


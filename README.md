# Prismic integration module

## Description
API connection between Prismic and Magento

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Prismic ViewModel](#prismic-viewmodel)
- [Slug ViewModel](#slug-viewmodel)
- [Title ViewModel](#title-viewmodel)
- [Layout XML](#layout-xml)
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
use Magento\Framework\View\LayoutInterface;
use Marleen\PrismicIntegration\Model\Slices\ButtonSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\CtaSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\HeroSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\ReviewsSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\StepsSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\TitleWithTextSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\UspsSliceInterface;
```

### Properties

The class has one protected property:

```php
protected LayoutInterface $layout;
```

This is used to handle layout rendering in Magento.

### Methods

#### `displaySlices`

This method renders HTML for provided slices using their respective Magento blocks. It loops through the slices and checks if each slice type has a corresponding block configuration:

```php
 /**
 * Render HTML for provided slices using their respective Magento blocks.
 *
 * @param array $slices
 * @param mixed $block
 * @return string
 */
public function displaySlices(array $slices, $block): string
{
    $htmlOutput = '';
    $sliceMap = $this->getSliceMap();

    foreach ($slices as $slice) {
        $type = $slice->slice_type;
        if (!isset($sliceMap[$type])) {
            continue;
        }

        $config = $sliceMap[$type];
        $childBlock = $block->getChildBlock($config['blockName']);
        if (!$childBlock) {
            continue;
        }

        $attributes = $this->getAttributesForSlice($slice, $config);
        $this->setSliceAttributes($slice, $childBlock, $attributes);
        $this->setVariation($slice->variation, $childBlock);

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
#### `getAttributesForSlice`

Get attributes for a given slice.

```php
 /**
 * Get attributes for a given slice.
 *
 * @param object $slice
 * @param array $config
 * @return array
 */
protected function getAttributesForSlice(object $slice, array $config): array
{
    $attributes = $config['attributes'];

    if ($slice->variation !== 'default' && isset($config[$slice->variation]['attributes'])) {
        $attributes = $config[$slice->variation]['attributes'];
    }

    return $attributes;
}
```

#### `setSliceAttributes`

This protected method sets attributes on child blocks based on the slice's primary attributes. It handles special cases like reviews and other list attributes:

```php
 /**
 * Set attributes on child blocks based on the slice's primary attributes.
 *
 * @param object $slice
 * @param mixed $childBlock
 * @param array $attributes
 */
protected function setSliceAttributes(object $slice, $childBlock, array $attributes): void
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
/**
 * Set the variation name of a block based on the slice's variation property.
 *
 * @param string $variation
 * @param mixed $childBlock
 */
protected function setVariation(string $variation, $childBlock): void
{
    $childBlock->setData('blockVariation', $variation);
}
```
### Summary

- **Purpose**: The `Prismic` ViewModel is used to integrate Prismic content into a Magento storefront.
- **Key Methods**: 
  - `displaySlices()` renders slices using Magento blocks.
  - `getSliceMap()` maps slice types to their blocks and attributes.
  - `setSliceAttributes()` sets attributes on child blocks based on slice data.
  - `setVariation()` set the variation name of a block based on the slice's variation property

## Slug ViewModel
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

## Title ViewModel
### Methods 
#### `getTitle`

This method retrieves the page title with the Prismic API: 

```php
public function getTitle() :string
{
    // Get the Prismic API instance from the API model
    $api = $this->apiModel->getApi();
    // Get the slug
    $slug = $this->slugModel->getSlug();

    // Fetch the document from the Prismic API using the slug
    $document = $api->getByUID('page', $slug);
    return $document->data->title[0]->text; // Return page title
}
```
## Layout XML

The following XML configuration defines the layout for integrating Prismic content into a Magento page. This file includes references to CSS, blocks for rendering various Prismic slices, and arguments for necessary ViewModel classes.

### Head Section

The `<head>` section includes a reference to a CSS file and sets a default title. The CSS gets generated with Tailwind. The title gets replaced with the title retrieved form Prismic:

```xml
<head>
    <css src="Marleen_PrismicIntegration::css/output.css" />
    <title>Your Default Title</title>
</head>
```

### Body Section

The `<body>` section defines the main container and includes blocks for rendering Prismic content slices. Each block references a template file and includes arguments for ViewModel classes.

```xml
<body>
    <referenceContainer name="main">
        <block name="prismic.content" template="Marleen_PrismicIntegration::prismic.phtml">
            <arguments>
                <argument xsi:type="object" name="prismicModel">\Marleen\PrismicIntegration\ViewModel\Prismic</argument>
                <argument xsi:type="object" name="slug">\Marleen\PrismicIntegration\ViewModel\Slug</argument>
                <argument xsi:type="object" name="apiModel">\Marleen\PrismicIntegration\ViewModel\Api</argument>
            </arguments>
            <block name="slices.hero" template="Marleen_PrismicIntegration::slices/hero.phtml"/>
            <block name="slices.button" template="Marleen_PrismicIntegration::slices/button.phtml"/>
            <block name="slices.cta" template="Marleen_PrismicIntegration::slices/cta.phtml"/>
            <block name="slices.reviews" template="Marleen_PrismicIntegration::slices/reviews.phtml">
                <arguments>
                    <argument xsi:type="object" name="reviewsModel">\Marleen\PrismicIntegration\ViewModel\Reviews</argument>
                    <argument xsi:type="object" name="linkResolver">\Marleen\PrismicIntegration\ViewModel\LinkResolver</argument>
                    <argument xsi:type="object" name="htmlSerializer">\Marleen\PrismicIntegration\ViewModel\HtmlSerializer</argument>
                </arguments>
            </block>
            <block name="slices.steps" template="Marleen_PrismicIntegration::slices/steps.phtml">
                <arguments>
                    <argument xsi:type="object" name="linkResolver">\Marleen\PrismicIntegration\ViewModel\LinkResolver</argument>
                    <argument xsi:type="object" name="htmlSerializer">\Marleen\PrismicIntegration\ViewModel\HtmlSerializer</argument>
                </arguments>
            </block>
            <block name="slices.titleWithText" template="Marleen_PrismicIntegration::slices/titleWithText.phtml">
                <arguments>
                    <argument xsi:type="object" name="linkResolver">\Marleen\PrismicIntegration\ViewModel\LinkResolver</argument>
                    <argument xsi:type="object" name="htmlSerializer">\Marleen\PrismicIntegration\ViewModel\HtmlSerializer</argument>
                </arguments>
            </block>
            <block name="slices.usps" template="Marleen_PrismicIntegration::slices/usps.phtml">
                <arguments>
                    <argument xsi:type="object" name="linkResolver">\Marleen\PrismicIntegration\ViewModel\LinkResolver</argument>
                    <argument xsi:type="object" name="htmlSerializer">\Marleen\PrismicIntegration\ViewModel\HtmlSerializer</argument>
                </arguments>
            </block>
        </block>
    </referenceContainer>
</body>
```

### Summary

- **Purpose**: The layout XML file configures the structure and content rendering for a Magento page using Prismic slices.
- **Sections**:
  - **Head**: Includes CSS and sets the default title.
  - **Body**: Contains blocks for rendering different Prismic slices, with each block referencing specific templates and passing necessary ViewModel arguments.
- **Key Blocks**:
  - `prismic.content`: Main block for Prismic content.
  - `slices.hero`: Renders hero slices.
  - `slices.button`: Renders button slices.
  - `slices.cta`: Renders call-to-action slices.
  - `slices.reviews`: Renders reviews slices with additional arguments for reviews model, link resolver, and HTML serializer.
  - `slices.steps`: Renders steps slices with additional arguments.
  - `slices.titleWithText`: Renders title with text slices with additional arguments.
  - `slices.usps`: Renders unique selling points slices with additional arguments.
 
## Prismic Template

The following PHP template file is responsible for fetching content from the Prismic CMS and rendering it within a Magento storefront. This template utilizes ViewModel classes to interact with the Prismic API, retrieve the appropriate content slices, and display them using predefined blocks.

### Explanation

1. **Variable Declarations**: The template starts with variable declarations for the block and the ViewModel instances (`Api`, `Prismic`, and `Slug`).

    ```php
    /**
     * @var $block \Magento\Framework\View\Element\Template
     * @var $apiModel \Marleen\PrismicIntegration\ViewModel\Api
     * @var $prismicModel \Marleen\PrismicIntegration\ViewModel\Prismic
     * @var $slugModel \Marleen\PrismicIntegration\ViewModel\Slug
     */
    ```

2. **Retrieve Prismic API Model**: The Prismic API model is retrieved from the block's data, and an instance of the Prismic API is obtained.

    ```php
    $apiModel = $block->getData('apiModel');
    $api = $apiModel->getApi();
    ```

3. **Retrieve Slug Model**: The Slug model is retrieved from the block's data, and the slug is obtained.

    ```php
    $slugModel = $block->getData('slug');
    $slug = $slugModel->getSlug();
    ```

4. **Fetch Document**: The document is fetched from the Prismic API using the slug. Slices are extracted from the document data if available.

    ```php
    $document = $api->getByUID('page', $slug);
    $slices = $document->data->slices ?? [];
    ```

5. **Display Slices**: The Prismic model is retrieved from the block's data, and the slices are displayed using the `displaySlices` method from the [Prismic ViewModel](#prismic-viewmodel).

    ```php
    $prismicModel = $block->getData('prismicModel');
    echo $prismicModel->displaySlices($slices, $block);
    ```

### Summary

- **Purpose**: This template fetches content slices from the Prismic CMS and renders them within a Magento storefront using ViewModel classes.
- **Key Steps**:
  - Retrieve the Prismic API model and obtain an API instance.
  - Retrieve the Slug model and obtain the slug.
  - Fetch the document from the Prismic API using the slug.
  - Extract slices from the document data.
  - Display the slices using the `displaySlices` method of the Prismic model.

## Contributing
[Kasper Beljaars](https://github.com/KasperOfzeau)


# Prismic integration module

## Description
API connection between Prismic and Magento

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Prismic ViewModel](#prismic-viewmodel)
- [SliceMap ViewModel](#slicemap-viewmodel)
- [Layout XML](#layout-xml)
- [Prismic Template](#prismic-template)
- [Slice Template](#slice-template)
- [CustomType ViewModel](#customtype-viewmodel)
- [Usps ViewModel](#usps-viewmodel)
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

## SliceMap ViewModel

#### `getSliceMap`

This method maps slice types to their corresponding block names and attributes required for rendering:

```php
public function getSliceMap(): array
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



## Layout XML

The following XML configuration defines the layout for integrating Prismic content into a Magento page. This file includes references to CSS, blocks for rendering various Prismic slices, and arguments for necessary ViewModel classes.

### Head Section

The `<head>` section includes a reference to a CSS file. The CSS gets generated with Tailwind. 
```xml
<head>
    <css src="Marleen_PrismicIntegration::css/output.css" />
</head>
```

### Body Section

The `<body>` section defines the main container and includes the header usps and the blocks for rendering Prismic content slices. Each block references a template file and includes arguments for ViewModel classes.

```xml
<body>
    <referenceContainer name="page.wrapper">
        <block name="header.usps" template="Marleen_PrismicIntegration::header/usps.phtml" before="header.content">
            <arguments>
                <argument name="uspsModel" xsi:type="object">\Marleen\PrismicIntegration\ViewModel\Usps</argument>
            </arguments>
        </block>
    </referenceContainer>
    <referenceContainer name="main">
        <block name="page.title" class="Elgentos\PrismicIO\Block\Layout\PageTitle">
            <block class="Elgentos\PrismicIO\Block\Dom\Text" template="data.title"/>
        </block>
        <block name="prismic.content" template="Marleen_PrismicIntegration::prismic.phtml">
            <arguments>
                <argument xsi:type="object" name="prismicModel">\Marleen\PrismicIntegration\ViewModel\Prismic</argument>
                <argument xsi:type="object" name="documentModel">\Marleen\PrismicIntegration\ViewModel\Document</argument>
            </arguments>
            <block name="slices.hero" template="Marleen_PrismicIntegration::slices/hero.phtml"/>
            <block name="slices.button" template="Marleen_PrismicIntegration::slices/button.phtml"/>
            <block name="slices.cta" template="Marleen_PrismicIntegration::slices/cta.phtml"/>
            <block name="slices.reviews" template="Marleen_PrismicIntegration::slices/reviews.phtml">
                <arguments>
                    <argument xsi:type="object" name="customTypeModel">\Marleen\PrismicIntegration\ViewModel\CustomType</argument>
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
  - **Body**: Contains blocks for rendering different Prismic slices and the header usps, with each block referencing specific templates and passing necessary ViewModel arguments.
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

1. **Variable Declarations**: The template starts with variable declarations for the block and the ViewModel instances (`Template`, `Prismic`, and `Document`).

```php
/**
* @var $block \Magento\Framework\View\Element\Template
* @var $prismicModel \Marleen\PrismicIntegration\ViewModel\Prismic
* @var $documentModel \Marleen\PrismicIntegration\ViewModel\Document
*/
```

2. **Fetch Document**: The document is fetched from Elgentos module

```php
// Get documentModel
$documentModel = $block->getData('documentModel');
// Get document from Elgentos module
$document = $documentModel->getDocument();
```

5. **Display Slices**: The slices are displayed using the `displaySlices` method from the [Prismic ViewModel](#prismic-viewmodel).

```php
if ($document !== null) {
    // Get slices from document
    $slices = $document->data->slices ?? [];
    $prismicModel = $block->getData('prismicModel');
    // Display the slices using the Prismic model's displaySlices method
    echo $prismicModel->displaySlices($slices, $block);
}
```

### Summary

- **Purpose**: This template fetches content slices from Prismic and renders them within a Magento storefront using ViewModel classes.
- **Key Steps**:
  - Fetch the document from the Prismic API using the slug.
  - Extract slices from the document data.
  - Display the slices using the `displaySlices` method of the Prismic model.
 
## Slice Template

This PHP template file is used to render a hero slice within a Magento storefront. It includes a background image, a text heading, and optionally a button if the slice variation is 'heroWithButton'. The template ensures proper escaping of HTML attributes and content to enhance security. This is just an example of all the slices

### Explanation

1. **Variable Declarations**: The template starts with variable declarations for the block, escaper, and slice interface.

```php
/**
 * @var $block \Magento\Framework\View\Element\Template
 * @var $escaper \Magento\Framework\Escaper
 * @var $slice Marleen\PrismicIntegration\Model\Slices\HeroSliceInterface
 */
```

2. **Retrieve Slice Block Data**: The slice block data is retrieved from the block, including text, background image, variation, button text, and button link.

```php
$sliceBlock = $block->getData('slice');
$text = $sliceBlock->getText();
$background = $sliceBlock->getBackgroundImage();
$variation = $block->getData('blockVariation');
$buttonText = $sliceBlock->getButtonText();
$buttonLink = $sliceBlock->getButtonLink();
```

3. **Conditional Rendering**: The template checks if the background image URL and text are not empty before rendering the section.

```php
if (!empty($background->url) && !empty($text)):
```

4. **HTML Structure**: The HTML structure includes a section with a background image, a centered text heading, and an optional button if the variation is 'heroWithButton'.

```php
<section class="relative">
    <figure class="absolute inset-0">
        <img src="<?= $escaper->escapeHtmlAttr($background->url) ?>" class="pointer-events-none select-none object-cover h-[300px] md:h-full w-full" alt="">
    </figure>
    <div class="relative flex justify-center bg-hero w-full">
        <div class="flex flex-col justify-end items-end h-[300px] md:h-[500px]">
            <h2 class="text-white max-w-[1126px] text-3xl md:text-7xl text-center title-shadow mb-5 mx-10 md:mx-20"><?= $escaper->escapeHtml($text) ?></h2>
            <?php if ($variation == 'heroWithButton' && !empty($buttonText) && !empty($buttonLink)): ?>
                <a class="button mb-5 mx-auto" href="<?= $escaper->escapeHtmlAttr($buttonLink->url) ?>">
                    <?= $escaper->escapeHtml($buttonText) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
```
## CustomType ViewModel

ViewModel to fetch custom types from Prismic by ID or type

### `getByID`
```php
public function getByID($ids): \stdClass
{
    $api = $this->api->getApi();
    return $api->getByIDs($ids);
}
```
### `getByType`
```php
public function getByType($type): \stdClass
{
    $api = $this->api->getApi();
    return $api->query(Predicates::at('document.type', $type));
}
```

## Usps ViewModel

#### `getUsps`
Retrieves the header usps

```php
public function getUsps() : ?array
{
    // Fetch header usps from Prismic
    $document = $this->customType->getByType('message_bar');
    // Sort usps messages in an array
    $usps = [];
    if (isset($document->results[0]->data->messages) && !empty($document->results[0]->data->messages)) {
        foreach ($document->results[0]->data->messages as $message) {
            if (isset($message)) {
                $usps[] = $message;
            }
        }
        return $usps;
    } else {
        return null;
    }
}
```

    
## Contributing
[Kasper Beljaars](https://github.com/KasperOfzeau)


<?php

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

class Prismic implements ArgumentInterface
{
    protected LayoutInterface $layout;

    /**
     * Prismic constructor.
     *
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    // Map slice types to their corresponding block names and attributes for rendering
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
            CtaSliceInterface::TYPE => [
                'blockName' => CtaSliceInterface::BLOCK_NAME,
                'attributes' => CtaSliceInterface::ATTRIBUTES
            ],
            ReviewsSliceInterface::TYPE => [
                'blockName' => ReviewsSliceInterface::BLOCK_NAME,
                'attributes' => ReviewsSliceInterface::ATTRIBUTES
            ],
            StepsSliceInterface::TYPE => [
                'blockName' => StepsSliceInterface::BLOCK_NAME,
                'attributes' => StepsSliceInterface::ATTRIBUTES
            ],
            TitleWithTextSliceInterface::TYPE => [
                'blockName' => TitleWithTextSliceInterface::BLOCK_NAME,
                'attributes' => TitleWithTextSliceInterface::ATTRIBUTES
            ],
            UspsSliceInterface::TYPE => [
                'blockName' => UspsSliceInterface::BLOCK_NAME,
                'attributes' => UspsSliceInterface::ATTRIBUTES
            ],
        ];
    }

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

    /**
     * Convert attribute names from camelCase to snake_case.
     *
     * @param string $input
     * @return string
     */
    protected function camelCaseToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

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
}

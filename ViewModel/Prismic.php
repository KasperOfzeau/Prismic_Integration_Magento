<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;

class Prismic implements ArgumentInterface
{
    protected LayoutInterface $layout;
    protected SliceMap $sliceMap;

    /**
     * Prismic constructor.
     *
     * @param LayoutInterface $layout
     * @param SliceMap $sliceMap
     */
    public function __construct(LayoutInterface $layout, SliceMap $sliceMap)
    {
        $this->layout = $layout;
        $this->sliceMap = $sliceMap;
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
        $sliceMap = $this->sliceMap->getSliceMap();

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
                $value = array_map(fn($item) => $item->review->id, $slice->primary->reviews);
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
    protected function setVariation(string $variation, mixed $childBlock): void
    {
        $childBlock->setData('blockVariation', $variation);
    }
}

<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Marleen\PrismicIntegration\Model\Slices\ButtonSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\CtaSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\HeroSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\ReviewsSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\StepsSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\TitleWithTextSliceInterface;
use Marleen\PrismicIntegration\Model\Slices\UspsSliceInterface;

class SliceMap implements ArgumentInterface
{
    // Map slice types to their corresponding block names and attributes for rendering
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
}

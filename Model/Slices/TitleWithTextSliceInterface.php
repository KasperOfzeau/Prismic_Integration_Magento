<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface TitleWithTextSliceInterface
{
    public const TYPE = 'title_with_text';
    public const BLOCK_NAME = 'slices.titleWithText';
    public const ATTRIBUTES = [
        'texts',
    ];
    public function getTexts();
}

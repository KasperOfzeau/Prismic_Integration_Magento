<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface ButtonSliceInterface
{
    public const TYPE = 'button';
    public const BLOCK_NAME = 'slices.button';
    public const ATTRIBUTES = [
        'text',
        'link',
        'color'
    ];
    public function getText();
    public function getLink();
    public function getColor();
}

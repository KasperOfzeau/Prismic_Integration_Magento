<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface CtaSliceInterface
{
    public const TYPE = 'cta';
    public const BLOCK_NAME = 'slices.cta';
    public const ATTRIBUTES = [
        'icon',
        'title',
    ];
    public function getIcon();
    public function getTitle();
}

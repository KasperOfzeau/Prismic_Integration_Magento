<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface HeroSliceInterface
{
    public const TYPE = 'hero';

    public const BLOCK_NAME = 'slices.hero';

    public const DEFAULT = [
        'backgroundImage',
        'text',
    ];

    public const HEROWITHBUTTON = [
        'backgroundImage',
        'text',
        'buttonText',
        'buttonLink',
    ];

    public function getBackgroundImage(): string;

    public function getText(): string;
    public function getButtonText(): string;
    public function getButtonLink(): string;
}

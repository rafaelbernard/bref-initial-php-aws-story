<?php

namespace BrefStory\Domain;

interface ImageService
{
    public function getImageFor(int $imagePixels): array;
}

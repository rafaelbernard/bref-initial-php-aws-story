<?php

namespace BrefStory\Application;

class SampleService
{
    public function getImageFor(): string
    {
        $ch = \curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://picsum.photos/200');
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $output = \curl_exec($ch);
        curl_close($ch);

        die($output);
    }
}

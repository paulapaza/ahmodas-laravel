<?php

require "vendor/autoload.php";

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleVision
{
    private $image;
    private $type;
    private $maxResults;
    private $features;
    private $key;

    public function __construct()
    {
        $this->key = json_decode(file_get_contents('key.json'), true);
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    public function setFeatures($features)
    {
        $this->features = $features;
    }

    public function request()
    {
        $imageAnnotator = new ImageAnnotatorClient($this->key);
        $image = file_get_contents($this->image);
        $response = $imageAnnotator->annotateImage(
            (new \Google\Cloud\Vision\V1\Image())
                ->setContent($image),
            [
                'features' => [
                    [
                        'type' => $this->features,
                        'maxResults' => $this->maxResults
                    ]
                ]
            ]
        );
        return $response;
    }


    
}

<?php

namespace RecognizeIm\Client;

use RecognizeIm\Configuration;
use RecognizeIm\RecognizeImAPIResult;
use RecognizeIm\Result\RecognizeResult;
use RecognizeIm\Service\ImageVerificator;

class RestApi
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var ImageVerificator
     */
    private $imageVerificator;

    /**
     * Constructor
     *
     * @param string $clientId
     * @param string $apiKey
     * @param ImageVerificator $imageVerificator
     */
    public function __construct(
        $clientId,
        $apiKey,
        ImageVerificator $imageVerificator
    ) {
        $this->clientId         = $clientId;
        $this->apiKey           = $apiKey;
        $this->imageVerificator = $imageVerificator;
    }

    /**
     * Build url for query
     *
     * @param string $mode
     * @param bool $getAll
     * @return string
     */
    private function buildUrl($mode, $getAll)
    {
        $url = Configuration::URL.'v2/recognize/'.$mode.'/';

        if (true === $getAll) {
            $url .= 'all/';
        }

        $url .= $this->clientId;

        return $url;
    }

    /**
     * Recognize object using image in single mode
     *
     * @param $image query
     * @param $mode Recognition mode. Should be 'single' or 'multi'. Default is 'single'.
     * @param $getAll if TRUE returns all recognized objects in 'single' mode, otherwize only the best one; in 'multi' it enables searching for multiple instances of each object
     * @return RecognizeResult
     */
    public function recognize($image, $mode = 'single', $getAll = false)
    {
        $curl = curl_init($this->buildUrl($mode, $getAll));

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $image);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'x-itraff-hash: '.md5($this->apiKey.$image),
            'Content-type: image/jpeg'
        ));

        $obj    = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $res = array();

        if ($status !== 200) {
            $res = array(
                'status'  => -1,
                'message' => 'Cannot upload photo'
            );
        } else {
            $res = json_decode($obj);
        }

        return new RecognizeResult($res);
    }
}
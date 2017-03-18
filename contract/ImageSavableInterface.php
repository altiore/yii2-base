<?php

namespace altiore\base\contract;

/**
 * Created by PhpStorm.
 * User: altiore
 * Date: 18.03.17
 * Time: 22:38
 */
interface ImageSavableInterface
{
    /**
     * @return string - relative path to image folder for current object
     */
    public function getImagePath();
}
<?php

namespace Blage;

/**
 * Description of TwitterService
 *
 * @author srohweder
 */
class TwitterService
{
    protected $twitter;

    public function __construct(Twitter $twitter) {
        $this->twitter = $twitter;
    }
}

<?php

namespace Hossein\Gateway;

class Base
{
    /**
     * supporting fa,en
     * @var string Language to show errors, messages & exceptions
     */
    protected $lang = 'fa';

    public function __construct()
    {
        require_once ('../vendor/autoload.php');
    }

    public function valid_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE)
            return false;

        return true;
    }
}
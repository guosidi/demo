<?php

if (!function_exists('C')) {

    function C($className)
    {
        return LtObjectUtil::singleton($className);
    }
}
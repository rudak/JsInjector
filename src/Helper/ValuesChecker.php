<?php

namespace Rudak\JsInjector\Helper;

class ValuesChecker
{
    /**
     * Vérifie si tous les indexes sont des chaines
     *
     * @param array $values
     * @return bool|int|string
     */
    public static function isValid(array $values)
    {
        foreach ($values as $key => $value) {
            if (is_numeric ($key)) {
                return $key;
            }
        }
        return true;
    }
}
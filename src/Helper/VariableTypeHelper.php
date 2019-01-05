<?php

namespace Rudak\JsInjector\Helper;

class VariableTypeHelper
{
    /**
     * Renvoie le type de la variable injectée
     *
     * @param $value
     * @return string
     */
    public static function getVariableType($value)
    {
        $type = gettype($value);
        switch ($type) {
            case 'array':
                return sprintf('%s [Elements: %d, Max depth: %d]',
                    $type,
                    count($value),
                    self::array_depth($value)
                );
                break;
            default:
                return $type;
        }
    }

    /**
     * Renvoie la profondeur d'un tableau
     *
     * @param $array
     * @return float|int
     */
    private static function array_depth($array)
    {
        $max_indentation = 1;

        $array_str = print_r($array, true);
        $lines     = explode("\n", $array_str);

        foreach ($lines as $line) {
            $indentation = (strlen($line) - strlen(ltrim($line))) / 4;

            if ($indentation > $max_indentation) {
                $max_indentation = $indentation;
            }
        }

        return ceil(($max_indentation - 1) / 2) + 1;
    }

}
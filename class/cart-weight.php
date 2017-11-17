<?php

class CartWeight
{

    public static function toGram($number, $type)
    {
        $weightUnits = array(
            'kg'  => 1000,
            'g'   => 1,
            'lbs' => 453.592,
            'oz'  => 28.3495,
        );

        if (array_key_exists($type, $weightUnits)) {
            $result = $number * $weightUnits[$type];

            return $result;
        }

        return false;
    }

}

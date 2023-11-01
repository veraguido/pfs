<?php

namespace PFS;

use Error;
use JetBrains\PhpStorm\NoReturn;

class Validations {
    public static function validateCLIInput(array $inputArray): bool
    {
        if (
            count($inputArray) == 2 &&
            (
                $inputArray[1] == 'help' ||
                $inputArray[1] == '--help' ||
                $inputArray[1] == '-h'
            )
        ) {
            Output::printHelpAndExit();
        }

        if (count($inputArray) < 5 ) {
            Output::wrongParamSet();
        }


        return (
            is_array(explode(',', $inputArray[1])) &&
            is_int(intval($inputArray[2])) &&
            self::isValidDate($inputArray[3]) &&
            is_float(floatval($inputArray[4]))
        );
    }


    protected static function isValidDate(string $date): bool
    {
        list($year, $month, $day) = explode('-', $date);
        try{
            $result  = checkdate($month, $day, $year);
        } catch (Error $error) {
            return false;
        }
        return $result;
    }
}

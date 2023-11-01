<?php

namespace PFS;

use JetBrains\PhpStorm\NoReturn;

class Output
{
    /**
     * @return void
     */
    #[NoReturn] public static function printHelpAndExit(): void
    {
        echo 'config: '.PHP_EOL.'
        trialsToRunPerPlan | integer | number of trials to be run per plan on each day.'.PHP_EOL.' 
        cycleTimes | string | comma separated cycle floats representing number of days of the last items, the more cycle times the more accurate the estimation will be '.PHP_EOL.'
        cycleTimesVariationMin | integer | Percentage number where every team has a minimum chance of delivering something before the estimation example, there is a 10% chance of delivering something before time, this is represented by the 90% variation'.PHP_EOL.' 
        cycleTimesVariationMax | integer | Percentage number where every team has a maximum chance of delivering something after the estimation example, there is a 40% chance of delivering something before time, this is represented by the 140% variation'.PHP_EOL.'
        itemsToEstimate | integer | Number of items you need to estimate'.PHP_EOL.'
        itemsToEstimateVariationMin | integer | Growth percentage minimum of your work. For instance, a team can have a minimum 30% growth in bug items or scope creep'.PHP_EOL.'
        itemsToEstimateVariationMax | integer | Growth percentage maximum of your work. For instance, a team can have a maximum 50% growth in bug items or scope creep'.PHP_EOL.'
        parallelismFactorVariationMin | integer | Depending on the size of your team this can vary, this is the minimum estimated number of resources working on this set of items at the same time in average'.PHP_EOL.'
        parallelismFactorVariationMax | integer | Depending on the size of your team this can vary, this is the maximum number of resources working on this set of items at the same time in average'.PHP_EOL.'
        startDate | string | yy-mm-dd of the date your team is starting the work'.PHP_EOL.'
        acceptableConfidence | integer | Acceptable confidence in percentage, recommended is 85'.PHP_EOL.'
        centralTendency | string | Method used to calculate central tendency values can be median|mean|mode only'.PHP_EOL.'
        parallelismFactor | float | the estimated amount of resources that will be working at the same time'.PHP_EOL;
        exit();
    }

    #[NoReturn] public static function wrongParamSet(): void
    {
        echo 'Something is wrong with the configuration. Please review it and try again' . PHP_EOL;
        self::printHelpAndExit();
    }

    #[NoReturn] public static function reportConfidence(int $confidence, string $targetDate): void
    {
        echo 'Target Date: ' . $targetDate . PHP_EOL;
        echo 'Confidence level: ' . $confidence . PHP_EOL;
    }

}
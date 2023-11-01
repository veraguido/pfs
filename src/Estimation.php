<?php

namespace PFS;
use DateTime;
use Exception;

class Estimation
{
    public int $trialAmount;
    public int $acceptableConfidence;
    protected DateTime $currentTargetDate;

    protected array $config;
    /**
     * @param ?array $cycleTimes array of cycle times used for running the trials with randomised values
     * @param ?int $amountOfItems How many items does the new work requires
     * @param ?string $startDate starting point date you want to run the simulation from
     * @param ?float $parallelismFactor What's the factor that should be used as parallel work happening
     * @param ?string $centralTendency median as recommended, but mean and mode are acceptable values
     * @throws Exception
     */
    public function __construct(
        protected ?array $cycleTimes = null,
        protected ?int $amountOfItems = null,
        protected ?string $startDate = null,
        protected ?float $parallelismFactor = null,
        protected ?string $centralTendency = null
    ) {
        $this->config = yaml_parse_file(__DIR__ . "/../config/config.yml");;
        $this->cycleTimes = $this->cycletimes ?? explode(",", $this->config['cycleTimes']);
        $this->amountOfItems = $this->amountOfItems ?? $this->config['itemsToEstimate'];
        $this->startDate = $this->startDate ?? $this->config['startDate'];
        $this->parallelismFactor = $this->parallelismFactor ?? $this->config['parallelismFactor'];
        $this->currentTargetDate = new DateTime($this->startDate);
        $this->trialAmount = $this->config['trialsToRunPerPlan'];
        $this->acceptableConfidence = $this->config['acceptableConfidence'];
        $this->centralTendency = $this->centralTendency ?? $this->config['centralTendency'];
    }
    /**
     * @throws Exception
     */
    public function checkPlan(
        array $statisticalCycleTimes,
        int|float $statisticalAmountOfItems,
        int|float $statisticalParallelismFactor
    ): Plan
    {
        $method = 'get' . ucwords($this->centralTendency);
        try {
            $cycleTimeUnit = $this->$method($statisticalCycleTimes);
        } catch (\Error $e) {
            throw new Exception('Selected central tendency is invalid. Please use mean, median or mode' .PHP_EOL);
        }

        $amountOfConsecutiveWorkingDays = floor(
            ($statisticalAmountOfItems * $cycleTimeUnit) / $statisticalParallelismFactor
        );
        $startDate = new \DateTime($this->startDate);
        $endDate = $startDate->modify("+" . intval($amountOfConsecutiveWorkingDays) . " days");
        $estimatedDate = $this->currentTargetDate;
        $plan = new Plan($endDate, $estimatedDate);
        $plan->execute();
        return $plan;
    }
    /**
     * @throws Exception
     */
    public function checkPlanRisk(): Plan
    {
        //adding uncertainty to cycle times as delivery would happen between cycleTimesVariationMin and cycleTimesVariationMax
        $statisticCycleTimes = [];
        foreach($this->cycleTimes as $cycleTimeIndex => $cycleTimeValue) {
            $variation = (rand($this->config['cycleTimesVariationMin'], $this->config['cycleTimesVariationMax']) / 100);
            $statisticCycleTimes[$cycleTimeIndex] = $cycleTimeValue * $variation;
        }

        //adding uncertainty variation to the amount of items as the number usually increases due to unknowns or bugs
        $statisticalAmountOfItems =
            $this->amountOfItems * (rand($this->config['itemsToEstimateVariationMin'], $this->config['itemsToEstimateVariationMax']) / 100);

        //adding uncertainty variation in people working at the same time.
        $statisticalParallelFactor =
            $this->parallelismFactor * (rand($this->config['parallelismFactorVariationMin'], $this->config['parallelismFactorVariationMax']) / 100);

        return $this->checkPlan($statisticCycleTimes, $statisticalAmountOfItems, $statisticalParallelFactor);
    }
    /**
     * It will run the number of trials per day until finding +85% confidence for a date.
     * @return void
     * @throws Exception
     */
    public function runCheckPlanRisk(): void
    {
        $confidence = 0;
        while ($confidence < $this->acceptableConfidence) {
            $arriveOnTime = 0;
            for ($i = 0; $i <= $this->trialAmount; $i++) {
                $plan = $this->checkPlanRisk();
                if (true === $plan->getOnTime()) {
                    $arriveOnTime++;
                }
                $confidence = floor($arriveOnTime / ($i + 1) * 100);
            }
            $date = $this->currentTargetDate->format('Y-m-d');
            //TODO: add verbosity?
            Output::reportConfidence($confidence, $date);
            $this->currentTargetDate = $this->currentTargetDate->modify("+1 day");
        }
    }
    private function getMean(array $input): float
    {
        $inputArray = array_filter($input);
        return array_sum($inputArray)/count($inputArray);
    }
    private function getMedian(array $input): float
    {
        sort($input);
        $count = sizeof($input);
        $index = floor($count/2);

        if (!$count) {
            echo "no values" . PHP_EOL;
            exit();
        }

        if ($count & 1) {    // count is odd
            return $input[$index];
        } else {                   // count is even
            return ($input[$index-1] + $input[$index]) / 2;
        }
    }

    private function getMode(array $input)
    {
        $frequency = array();
        foreach ($input as $number) {
            if (!isset($frequency[$number])) {
                $frequency[$number] = 0;
            }
            $frequency[$number]++;
        }
        // Find the highest frequency
        $max_frequency = max($frequency);
        // Select the numbers with the highest frequency
        $mode = array();
        foreach ($frequency as $number => $count) {
            if ($count == $max_frequency) {
                $mode[] = $number;
            }
        }
        return $mode[0];
    }
}
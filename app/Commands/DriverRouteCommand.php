<?php

namespace App\Commands;

use App\Classes\MunkresAlgorithm;
use LaravelZero\Framework\Commands\Command;

class DriverRouteCommand extends Command
{
    private array $destinations = [];

    private array $drivers = [];

    private array $selectedDestinationKeys = [];

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param MunkresAlgorithm $mukresAlgorithm
     * @return int
     */
    public function handle(MunkresAlgorithm $mukresAlgorithm): int
    {
        $this->askForInputFiles();

        $matrixLength = count($this->destinations);
        $matrix = $this->makeDriverDestinationMatrix($matrixLength);

        $ssMatrix = $this->createSuitabilityScoreMatrix($matrix);
        $costMatrix = $this->createCostMatrix($ssMatrix);

        $mukresAlgorithm->initData($costMatrix);
        // Using the Munkres algorithm to determine the best driver for each destination based on the minimum cost per task.
        $assignmentMatrix = $mukresAlgorithm->runMunkres();

        $assignments = $this->pairDriverWithDestination($assignmentMatrix);
        $totalSuitableScore = $this->calculateTotalSuitableScore($ssMatrix);

        $this->printOutput($totalSuitableScore, $assignments);

        return 1;
    }

    /**
     * @return void
     */
    private function askForInputFiles(): void
    {
        $destinationsFilePath = '';
        $driversFilePath = '';

       try {
           while(!$destinationsFilePath) {
               $destinationsFilePath = $this->ask("Enter the destinations file path");
           }

           while(!$driversFilePath) {
               $driversFilePath = $this->ask("Enter the drivers file path");
           }

           $this->destinations = file($destinationsFilePath, FILE_IGNORE_NEW_LINES);

           $this->drivers = file($driversFilePath, FILE_IGNORE_NEW_LINES);
       } catch (\Exception $e) {
          $this->alert("Please provide a suitable entry to continue.");
          die;
       }
    }

    private function makeDriverDestinationMatrix(int $length): array
    {
        return array_fill(0, $length, array_fill(0, $length, 0));
    }

    private function createSuitabilityScoreMatrix(array $matrix): array
    {
        for($i = 0; $i < count($matrix); $i++) {
            for($j = 0; $j < count($matrix[$i]); $j++) {
                $matrix[$i][$j] = $this->calculateSuitableScore($this->drivers[$i], $this->destinations[$j]);
            }
        }

        return $matrix;
    }

    private function createCostMatrix(array $matrix): array
    {
        return array_map(function ($drivers) {
            // Turning SS into cost using a large number to use Munkres algorithm.
            return array_map(fn($destinationSS) => 1000 - $destinationSS, $drivers);
        }, $matrix);
    }

    private function pairDriverWithDestination(array $assignmentMatrix): array
    {
        return collect($assignmentMatrix)->map(function ($row, $key) {
            // Getting the key where the value is 1 (best suitableDestination).
            $destinationKey = array_search(1, $row);
            // Filling this array to have the list of suitable destination positions in the SS Matrix.
            $this->selectedDestinationKeys[] = $destinationKey;
            return [
               'driver' => $this->drivers[$key],
               'destination' => $this->destinations[$destinationKey]
            ];
        })->toArray();
    }

    private function calculateTotalSuitableScore(array $ssMatrix): float
    {
        return collect($ssMatrix)
            ->reduce(function ($total, $row, $key) {
                // Getting the value in the destination list that correspond to the key with correct SS.
                $destinationColumnKey = $this->selectedDestinationKeys[$key];

                return $total + $row[$destinationColumnKey];
            }, 0);
    }

    private function calculateSuitableScore(string $driver, string $destination): float
    {
        $baseSS = strlen($destination) % 2 === 0
            ? $this->getVowelsCount($driver) * 1.5 // Even
            : $this->getConsonantsCount($driver); // Odd

        // If the driver and address characters length has any common factors the SS is increased by 50%
        return $this->shareAnyCommonFactor($driver, $destination) ? $baseSS * 1.5 : $baseSS;
    }

    private function getConsonantsCount(string $name): int
    {
        return preg_match_all('/[bcdfghjklmnpqrstvwxyz]/i',$name);
    }

    private function getVowelsCount(string $name): int
    {
        return preg_match_all('/[aeiou]/i',$name);
    }

    private function shareAnyCommonFactor(string $driver, string $destination): bool
    {
        $driverLengthFactors = $this->getFactorsBesides1(strlen($driver));
        $destinationLengthFactors = $this->getFactorsBesides1(strlen($destination));

        return collect($destinationLengthFactors)->contains(fn($factor) => in_array($factor, $driverLengthFactors));
    }

    private function getFactorsBesides1(int $length): array
    {
        $factors = [];

        for($i = 2; $i < $length; $i++) {
            if($length % $i === 0) {
                $factors[] = $i;
            }
        }

        return $factors;
    }

    private function printOutput(float $total, array $shipments)
    {
        $this->info("Total Suitability Score: {$total}");
        $this->newline();

        foreach ($shipments as $key => $shipment) {
            $this->info("Shipment #" . $key + 1 . ":");
            $this->line("Driver: {$shipment['driver']}");
            $this->line("Destination: {$shipment['destination']}");
            $this->newline();
        }
    }
}

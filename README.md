
## Code Exercise

An application that assigns shipment destinations to drivers in a way that maximizes the suitability score (SS) based on a top-secret algorithm.

- Expecting well-formed input.
- Considering the white space between lines.
- Using [Munkres Algorithm](https://github.com/liqul/php-munkres) to determine the minimum cost for the task (destination) by the worker (driver)

<!-- GETTING STARTED -->
## Getting Started

### Prerequisites

* PHP >= 8.0
* Composer 2.0

### Installation

1. Clone the repository
   ```sh
   git clone https://github.com/yordi024/suitable-driver
   ```
2. Navigate to the application path
   ```sh
   cd suitable-driver
   ```
3. Install Composer packages
   ```sh
   composer install
   ```

### Usage

First create two text files, one with the list of drivers, and the other with the list of destinations. Each file should have the same number of lines, and every line should only contain one driver and destination.

1. Execute the app running the next command 
   ```sh
   php ss run
   ```
2. Then you would be asked to provide the path of each text file.
   ```sh
   Enter the destinations' file path: here paste the path of the destinations 
   Enter the drivers' file path: here paste the path of the drivers' list
   ```

### Output

Finally, if all goes well, the output should be the total suitability score and the shipment list with destination and driver.

 ```sh
Total Suitability Score: 16.5

Shipment #1:
Driver: jose perez
Destination: Calle Sabana Larga #40

Shipment #2:
Driver: JUAN
Destination: Calle Test Demo #1

Shipment #3:
Driver: pedro
Destination: Calle Demo Test #10
```

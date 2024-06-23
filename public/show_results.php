<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Controller\StatisticController;

$controller = new StatisticController();
$controller->showStatistic($_GET);
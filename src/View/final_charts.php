<?php
/** @var array<string, float> $evaluatedStatistic */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Загрузка статистики</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/chart.css">
</head>
<body>
<div class="chart-wrap vertical">
    <h2 class="title">Статистика по выбранной теме</h2>

    <div class="grid">
        <?php foreach ($evaluatedStatistic as $userName => $percentageOfExpertProximity): ?>
            <div class="bar" style="--bar-value:<?= $percentageOfExpertProximity ?>%" data-name="<?= $userName ?>" title="<?= $userName ?> <?= $percentageOfExpertProximity ?>"></div>
        <?php endforeach; ?>
    </div>
</div>
</html>
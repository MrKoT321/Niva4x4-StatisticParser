<!DOCTYPE html>
<html>
<head>
    <title>Загрузка статистики</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/form.css">
    <link rel="stylesheet" href="/css/loader.css">
</head>
<body>
    <div class="form-container">
        <form class="form" action="/load_statistic.php" method="POST" enctype="multipart/form-data">
            <div>
                <input type="text" name="url" id="url" class="form-field" required />
            </div>
            <div class="form-field form-field-full-width">
                <button type="submit" id="submit">Загрузить статистику</button>
                <span class="loader form-field-full-width" id="loader" style="display: none"></span>
            </div>
        </form>
    </div>
</body>
<script type="application/javascript" src="/js/form.js"></script>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href="<?php echo config('app_prefix',  '/') ?>" id="base_url">
    <link rel="stylesheet" href="<?php echo asset('app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="assets/font-awesome/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <title>Slim Inertia Svelte</title>
</head>
<body>
<?php if (!empty($inertiaHtml)) {
    echo $inertiaHtml;
} ?>
<script src="<?php echo asset('vendor.js') ?>"></script>
<script src="<?php echo asset('app.js') ?>"></script>
</body>
</html>

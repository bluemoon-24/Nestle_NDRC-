<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Nestle NDRC - Last Mile Visibility' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-surface-light">
    <!-- Navigation Placeholder -->
    <?php include_once BASE_PATH . '/app/Views/partials/navbar.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <?= $content ?>
    </main>

    <?php include_once BASE_PATH . '/app/Views/partials/footer.php'; ?>
</body>
</html>

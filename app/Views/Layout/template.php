<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyklistika 3. ročník</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('app/logo.png'); ?>">
    <?= $this->include('Layout/css') ?>
</head>

<body>
    <div class="container">
        <?= $this->renderSection('content') ?>
    </div>
    <?= $this->include('Layout/js') ?>
</body>

</html>
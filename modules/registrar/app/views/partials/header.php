<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/css/calendar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/css/dropdown.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.5/css/lightbox.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <title><?php echo $_ENV['APP_NAME'] ?? 'School Management System'; ?></title>
</head>
<body>
<header>
    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>

     <div class="header-status">
         SY
    <span id="schoolYearLabel">
        <?= $schoolYear['name']  ?? ''; ?>
    </span>•
    <span id="semesterLabel">
           <?= $semester['name'] ?? ''; ?>
    </span>
       
    <span id="live-clock"></span>
</div>
</header>


<?php include  __DIR__ .'/notifications.php'; ?>


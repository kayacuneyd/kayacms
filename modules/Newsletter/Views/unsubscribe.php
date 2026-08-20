<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Newsletter subscription</title>
    <style>body{font-family:Georgia,serif;max-width:680px;margin:64px auto;padding:0 24px;color:#1f2933;line-height:1.6}a{color:#0f766e}</style>
</head>
<body>
    <h1><?= $found ? 'You have been unsubscribed' : 'Link not found' ?></h1>
    <p><?= $found ? 'Your newsletter subscription has been deactivated.' : 'This unsubscribe link is invalid or has already been used.' ?></p>
    <p><a href="<?= base_url('/') ?>">Return to the homepage</a></p>
</body>
</html>

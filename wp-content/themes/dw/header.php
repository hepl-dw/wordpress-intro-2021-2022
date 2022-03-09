<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header class="header">
        <h1 class="header__title"><?= get_bloginfo('name'); ?></h1>
        <p class="header__tagline"><?= get_bloginfo('description'); ?></p>

        <nav class="header__nav nav">
            <h2 class="nav__title">Navigation principale</h2>
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class' => 'nav__links',
                'menu_id' => 'navigation',
                'container_class' => 'nav__container',
                'walker' => new PrimaryMenuWalker()
            ]); ?>
        </nav>
    </header>
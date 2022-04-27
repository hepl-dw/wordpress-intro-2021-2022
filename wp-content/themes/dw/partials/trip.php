<article class="trip">
    <a href="<?= get_the_permalink(); ?>" class="trip__link">Lire le récit de voyage "<?= get_the_title(); ?>"</a>
    <div class="trip__card">
        <header class="trip__head">
            <h3 class="trip__title"><?= get_the_title(); ?></h3>
            <p class="trip__date"><time class="trip__time" datetime="<?= date('c', strtotime(get_field('departure_date', false, false))); ?>">
                <?= ucfirst(date_i18n('F, Y', strtotime(get_field('departure_date', false, false)))); ?>
            </time></p>
        </header>
        <figure class="trip__fig">
            <?= get_the_post_thumbnail(null, 'medium_large', ['class' => 'trip__thumb']); ?>
        </figure>
    </div>
</article>
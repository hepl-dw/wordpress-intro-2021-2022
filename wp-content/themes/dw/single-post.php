<?php get_header(); ?>
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
    <main class="layout singlePost">
        <h2 class="singlePost__title"><?= get_the_title(); ?></h2>
        <figure class="singlePost__fig">
            <?= get_the_post_thumbnail(null, 'medium_large', ['class' => 'singlePost__thumb']); ?>
        </figure>
        <div class="singlePost__content">
            <?php the_content(); ?>
        </div>
    </main>
    <?php endwhile; endif; ?>
<?php get_footer(); ?>
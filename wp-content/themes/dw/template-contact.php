<?php /* Template Name: Contact page template */ ?>
<?php get_header(); ?>
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
    <main class="layout contact">
        <h2 class="contact__title"><?= get_the_title(); ?></h2>
        <div class="contact__content">
            <?php the_content(); ?>
        </div>
        <div class="contact__form">
            <?= apply_filters('the_content', '[contact-form-7 id="35" title="Formulaire de contact"]'); ?>
        </div>
    </main>
    <?php endwhile; endif; ?>
<?php get_footer(); ?>
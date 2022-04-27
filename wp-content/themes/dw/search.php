<?php get_header(); ?>

<main class="layout">
    <section class="results">
        <h2 class="results__title">Les articles correspondant à votre recherche</h2>
        <div class="results__container">
        <?php 
        if(have_posts()): while(have_posts()): the_post();
            dw_include('post', ['modifier' => 'search']); 
        endwhile; else: ?>
            <p class="results__empty">Il n'y a pas de résultats à afficher.</p>
        <?php endif; ?>
        </div>
    </section>
    <section class="results">
        <h2 class="results__title">Les récits de voyage correspondant à votre recherche</h2>
        <div class="results__container">
        <?php 
        if(($trips = dw_get_trips(3, get_search_query()))->have_posts()): while($trips->have_posts()): $trips->the_post();
            include(__DIR__ . '/partials/trip.php'); 
        endwhile; else: ?>
            <p class="results__empty">Il n'y a pas de résultats à afficher.</p>
        <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
<?php get_header(); ?>

<main class="layout">
    <section class="layout__latest latest">
        <h2 class="latest__title">Mes derniers articles</h2>
        <div class="latest__container">
            <?php
            if(have_posts()): while(have_posts()): the_post();
                dw_include('post', ['modifier' => 'index']); 
            endwhile; else: ?>
                <!-- Il n'y a pas d'articles à afficher -->
            <?php endif; ?>
        </div>
    </section>

    <section class="layout__trips trips">
        <h2 class="trips__title">Mes derniers voyages</h2>
        <div class="trips__container">
            <?php 
            if(($trips = dw_get_trips(3))->have_posts()): while($trips->have_posts()): $trips->the_post();
                include(__DIR__ . '/partials/trip.php');
            endwhile; else: ?>
            <p class="trips__empty">Il n'y a pas de voyages à vous raconter...</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
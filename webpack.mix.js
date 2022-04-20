const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix .setPublicPath('./wp-content/themes/dw/public')
    .js('wp-content/themes/dw/resources/js/script.js', 'wp-content/themes/dw/public/js/')
    .sass('wp-content/themes/dw/resources/sass/style.scss', 'wp-content/themes/dw/public/css/')
    .options({
        processCssUrls: false
    })
    .browserSync({
        proxy: 'wordpress-group-b.localhost',
        notify: false
    })
    .version();

const mix = require('laravel-mix');

mix.options({
    uglify: {
        uglifyOptions: {
            compress: {
                drop_console: true,
            },
        },
    },
});

mix .setPublicPath('public')
    .js('resources/js/app.js', 'public')
    .sass('resources/sass/app.scss', 'public')
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/fonts')
    .copy('resources/img', 'public/img');

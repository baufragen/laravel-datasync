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
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/fonts')
    .copy('resources/img', 'public/img')
    .copy('node_modules/json-tree-viewer/libs/jsonTree/icons.svg', 'public/img');

mix .sass('resources/sass/app.scss', 'public')
    .options({
        processCssUrls: false
    });
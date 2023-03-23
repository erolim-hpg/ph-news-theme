// webpack.mix.js

let mix = require('laravel-mix');

mix.disableSuccessNotifications();

// Compile
mix
.js('src/js/admin.js', 'js')
.sass('src/scss/admin.scss', 'css')
.setPublicPath('assets/');
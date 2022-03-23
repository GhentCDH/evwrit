var Encore = require('@symfony/webpack-encore');
var WebpackShellPlugin = require('webpack-shell-plugin');

Encore
    // the project directory where all compiled assets will be stored
    .setOutputPath('public/build/')

    // enable asset versioning, so browser caches don't need to be cleared
    .enableVersioning()

    // the public path used by the web server to access the previous directory
    .setPublicPath(Encore.isProduction() ? '/build/' : '/evwrit/public/build/')

    // allow pug templates in vue components
    .enableVueLoader()
    // .enableVueLoader( () => {}, {
    //     compiler: require('vue-template-babel-compiler')
    // })

    // Add javascripts
    .autoProvidejQuery()
    .addEntry('main', './assets/js/main/main.js')
    .addEntry('text-search', './assets/js/main/text-search.js')
    .addEntry('materiality-search', './assets/js/main/materiality-search.js')
    .addEntry('base-annotation-search', './assets/js/main/base-annotation-search.js')
    .addEntry('text-structure-search', './assets/js/main/text-structure-search.js')
    .addEntry('text-view', './assets/js/main/text-view.js')

    // allow sass/scss files to be processed
    .enableSassLoader()

    // Add stylesheets
    //.addStyleEntry('screen', './assets/scss/screen.scss')

    // provide source maps for dev environment
    .enableSourceMaps(!Encore.isProduction())

    // don't load chunks of code
    .disableSingleRuntimeChunk()

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // enable pug templates in vue
    .addLoader({
        test: /\.pug$/,
        loader: 'pug-plain-loader'
    })
;

Encore.addAliases({ vue$: 'vue/dist/vue.esm.js' });

// further config tweaking
const config = Encore.getWebpackConfig();

// Create symlinks using shell plugin
config.plugins.push(new WebpackShellPlugin({
    onBuildEnd: [
        './copy_assets.sh',
    ]
}));


// Make sure watch works
// https://github.com/symfony/webpack-encore/issues/191
// Use polling instead of inotify
config.watchOptions = {
    poll: true,
};

// Export the final configuration
module.exports = config;

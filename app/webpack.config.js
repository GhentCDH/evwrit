var Encore = require('@symfony/webpack-encore');
var WebpackShellPluginNext = require('webpack-shell-plugin-next');
var dotenv = require('dotenv')

dotenv.config({path: '.env.local'})

Encore
    // the project directory where all compiled assets will be stored
    .setOutputPath('public/build/')

    // enable asset versioning, so browser caches don't need to be cleared
    .enableVersioning(Encore.isProduction())
    // .enableVersioning()

    // the public path used by the web server to access the previous directory
    .setPublicPath(Encore.isProduction() ? '/build/' : '/build/')

    // allow pug templates in vue components
    .enableVueLoader(() => {}, { runtimeCompilerBuild: false })

    // // Enable TypeScript
    .enableTypeScriptLoader()

    // Add javascripts
    .autoProvidejQuery()

    .addEntry('main', './assets/js/main/main.js')
    .addEntry('text-search', './assets/js/main/text-search.js')
    .addEntry('materiality-search', './assets/js/main/materiality-search.js')
    .addEntry('base-annotation-search', './assets/js/main/base-annotation-search.js')
    .addEntry('text-structure-search', './assets/js/main/text-structure-search.js')
    .addEntry('text-view', './assets/js/main/text-view.js')

    // allow sass/scss files to be processed
    .enableSassLoader((options) => {
        options.sassOptions = {
            quietDeps: true, // disable warning msg
        }
    })

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

    .configureDevServerOptions(options => {
        options.allowedHosts = 'all';
        // options.port = '9000';
    })
;

Encore.configureDefinePlugin(options => {
    options['process.env'] = {
        VUE_APP_GTSA_URL: JSON.stringify(process.env.VUE_APP_GTSA_URL),
        VUE_APP_LTSA_URL: JSON.stringify(process.env.VUE_APP_LTSA_URL),
        VUE_APP_ORTHOGRAPHY_URL: JSON.stringify(process.env.VUE_APP_ORTHOGRAPHY_URL),
        VUE_APP_TYPOGRAPHY_URL: JSON.stringify(process.env.VUE_APP_TYPOGRAPHY_URL),
        VUE_APP_SYNTAX_URL: JSON.stringify(process.env.VUE_APP_SYNTAX_URL),
        VUE_APP_MORPHOLOGY_URL: JSON.stringify(process.env.VUE_APP_MORPHOLOGY_URL),
        VUE_APP_LEXIS_URL: JSON.stringify(process.env.VUE_APP_LEXIS_URL),
        VUE_APP_LANGUAGE_URL: JSON.stringify(process.env.VUE_APP_LANGUAGE_URL)
    };
});

Encore.addAliases({ vue$: 'vue/dist/vue.esm.js' });

// further config tweaking
const config = Encore.getWebpackConfig();

// Create symlinks using shell plugin
config.plugins.push(new WebpackShellPluginNext({
    onBuildEnd: {
        scripts: [
            './scripts/copy_assets.sh',
        ]
    }
}));


// Make sure watch works
// https://github.com/symfony/webpack-encore/issues/191
// Use polling instead of inotify
config.watchOptions = {
    poll: true,
};

// Export the final configuration
module.exports = config;

// Add TypeScript file extensions
config.resolve = {
    ...config.resolve,
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.vue', '.json']
};


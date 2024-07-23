/*
 * Load all available gulp plugins.
 */
import gulp from 'gulp';
import gulpLoadPlugins from 'gulp-load-plugins';
import tildeImporter from 'node-sass-tilde-importer';

const $ = gulpLoadPlugins({pattern: ['gulp-', 'gulp.*', 'del']});

/*
 * Task 'clean':
 * Remove all files in the directories where the generated files will be
 * located.
 */
gulp.task(
    'clean',
    $.del.bind(null, ['static/fonts/**', '!static/fonts', '!static/fonts/panno', '!static/fonts/panno/**', 'static/css', 'static/js'], {force: true})
);

/*
 * Task 'vendor':
 * Process vendor JavaScript and font files.
 */
gulp.task('vendor', () => {
    gulp.start('vendor-javascript');
    gulp.start('vendor-fonts');
});

/*
 * Task 'vendor-javascript':
 * (Compress and) copy vendor JavaScript files.
 */
gulp.task('vendor-javascript', () => {
    // Files that can be copied
    gulp.src(
        [
            'node_modules/bootstrap/dist/js/bootstrap.min.js',
            'node_modules/bootstrap-select/dist/js/bootstrap-select.min.js',
            'node_modules/bootstrap-select/dist/js/bootstrap-select.js.map',
            'node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            'node_modules/moment/min/moment.min.js',
            'node_modules/jquery/dist/jquery.min.js',
            'node_modules/jquery/dist/jquery.min.map',
            'node_modules/jquery-migrate/dist/jquery-migrate.min.js',
            'node_modules/smooth-scroll/dist/smooth-scroll.min.js'
        ]
    )
        .pipe(gulp.dest('static/js/vendor'));

    // Files that need to be placed in a specific folder
    gulp.src('node_modules/moment/locale/nl.js')
        .pipe($.sourcemaps.init())
        .pipe($.uglify())
        .pipe($.rename({
            suffix: '.min'
        }))
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest('static/js/vendor/locale'));

    // Files that need compressing
    gulp.src([
        'node_modules/bootstrap-sass/assets/javascripts/bootstrap/collapse.js',
        'node_modules/bootstrap-sass/assets/javascripts/bootstrap/modal.js',
        'node_modules/bootstrap-sass/assets/javascripts/bootstrap/alert.js',
        'node_modules/tocbot/dist/tocbot.js'
    ])
        .pipe($.sourcemaps.init())
        .pipe($.uglify())
        .pipe($.rename({
            suffix: '.min'
        }))
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest('static/js/vendor'));
});

/*
 * Task 'vendor-fonts':
 * Copy vendor font files.
 */
gulp.task('vendor-fonts', () => {
    // Bootstrap
    gulp.src([
        'node_modules/bootstrap/dist/fonts/*.*'
    ])
        .pipe(gulp.dest('static/fonts/bootstrap'));
    // Font Awesome
    gulp.src([
        'node_modules/font-awesome/fonts/*'
    ])
        .pipe(gulp.dest('static/fonts/font-awesome'));
});

/*
 * Task 'eslint':
 * Check own JavaScript code for potential errors.
 */
gulp.task('eslint', () => {
    return gulp.src([
        'gulpfile.babel.js',
        'js/*.js'
    ])
        .pipe($.eslint({
            globals: ['SmoothScroll']
        }))
        .pipe($.eslint.format())
        .pipe($.eslint.failAfterError());
});

/*
 * Task 'modernizr':
 * Detect which Modernizr tests are needed and build a custom Modernizr version.
 */
gulp.task('modernizr', () => {
    return gulp.src('js/*.js')
        .pipe($.modernizr('modernizr-custom.js'))
        .pipe($.sourcemaps.init())
        .pipe($.uglify())
        .pipe($.rename({
            suffix: '.min'
        }))
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest('static/js/vendor'));
});

/*
 * Task 'uglify':
 * Compress own JavaScript files to a single minified file.
 */
gulp.task('uglify', ['eslint'], () => {
    // Process main.js last, so variables / functions declared in other files can
    // be used
    return gulp.src([
        'js/!(main)*.js',
        'js/main.js'
    ])
        .pipe($.sourcemaps.init())
        .pipe($.concat('main.js'))
        .pipe($.uglify())
        .pipe($.rename({
            suffix: '.min'
        }))
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest('static/js'));
});

/*
 * Task 'sass':
 * Process all sass files and generate a single CSS file.
 */
gulp.task('sass', () => {
    return gulp.src('sass/*.scss')
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe($.sass({importer: tildeImporter, outputStyle: 'compressed', precision: 8}).on('error', $.sass.logError))
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest('static/css'));
});


gulp.task('javascript', ['uglify'], () => {
    return gulp.src([
        'static/js/vendor/modernizr-custom.min.js',
        'static/js/vendor/jquery.min.js',
        'static/js/vendor/jquery-migrate.min.js',
        'static/js/vendor/bootstrap.min.js',
        'static/js/vendor/modal.min.js',
        'static/js/vendor/bootstrap-select.min.js',
        'static/js/vendor/moment.min.js',
        'static/js/vendor/smooth-scroll.min.js',
        'static/js/vendor/locale/nl.min.js',
        'static/js/main.min.js'
    ])
        .pipe($.concat('bundle.min.js'))
        .pipe(gulp.dest('static/js'))
});

/*
 * Task 'watch':
 * Run the 'sass' task if one of the own sass files is modified.
 * Run the 'uglify' task if one of the own JavaScript files is modified.
 */
gulp.task('watch', () => {
    gulp.watch('sass/**/*.scss', ['sass']);
    gulp.watch('js/*.js', ['uglify']);
});

/*
 * Task 'build':
 * Build everything (JavaScript, fonts and CSS).
 */
gulp.task('build', ['clean'], () => {
    gulp.start('vendor');
    gulp.start('modernizr');
    gulp.start('uglify');
    gulp.start('sass');
});

/*
 * Task 'default':
 * Run the 'build' task if no specific task is given.
 */
gulp.task('default', () => {
    gulp.start('build');
});

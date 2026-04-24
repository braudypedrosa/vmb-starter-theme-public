const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const concat = require('gulp-concat');
const terser = require('gulp-terser');
const gulpIf = require('gulp-if');
const yargs = require('yargs');
const zip = require('gulp-zip');
const babel = require('gulp-babel');
const webpack = require('webpack-stream');

const argv = yargs.argv;
const isProduction = argv.prod;

function compileGeneralSCSS() {
  return gulp.src('src/general/scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('bundled-general.css'))
    .pipe(gulpIf(isProduction, cleanCSS()))
    .pipe(gulp.dest('dist/css'));
}

function compilePublicSCSS() {
  return gulp.src('src/public/scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('bundled-public.css'))
    .pipe(gulpIf(isProduction, cleanCSS()))
    .pipe(gulp.dest('dist/css'));
}

function compileAdminSCSS() {
  return gulp.src('src/admin/scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('bundled-admin.css'))
    .pipe(gulpIf(isProduction, cleanCSS()))
    .pipe(gulp.dest('dist/css'));
}

function compileGeneralJS() {
  return gulp.src('src/general/js/**/*.js')
    .pipe(webpack({
      mode: isProduction ? 'production' : 'development',
      output: {
        filename: 'bundled-general.js',
      },
      module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          }
        ]
      },
      devtool: !isProduction ? 'inline-source-map' : false,
    }))
    .pipe(gulpIf(isProduction, terser()))
    .pipe(gulp.dest('dist/js'));
}

function compilePublicJS() {
  return gulp.src('src/public/js/**/*.js')
    .pipe(webpack({
      mode: isProduction ? 'production' : 'development',
      output: {
        filename: 'bundled-public.js',
      },
      module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          }
        ]
      },
      devtool: !isProduction ? 'inline-source-map' : false,
    }))
    .pipe(gulpIf(isProduction, terser()))
    .pipe(gulp.dest('dist/js'));
}

function compileAdminJS() {
  return gulp.src('src/admin/js/**/*.js')
    .pipe(webpack({
      mode: isProduction ? 'production' : 'development',
      output: {
        filename: 'bundled-admin.js',
      },
      module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          }
        ]
      },
      devtool: !isProduction ? 'inline-source-map' : false,
    }))
    .pipe(gulpIf(isProduction, terser()))
    .pipe(gulp.dest('dist/js'));
}

function createZip() {
  return gulp.src([
      '**/*',
      '!node_modules/**',
      '!bundled/**',
      'dist/**',
      '!gulpfile.js',
      '!package-lock.json'
    ], { base: '.' })
    .pipe(zip('vmb-starter-theme.zip'))
    .pipe(gulp.dest('bundled'));
}

function watchFiles() {
  gulp.watch('src/general/scss/**/*.scss', compileGeneralSCSS);
  gulp.watch('src/public/scss/**/*.scss', compilePublicSCSS);
  gulp.watch('src/admin/scss/**/*.scss', compileAdminSCSS);
  gulp.watch('src/general/js/**/*.js', compileGeneralJS);
  gulp.watch('src/public/js/**/*.js', compilePublicJS);
  gulp.watch('src/admin/js/**/*.js', compileAdminJS);
}

const build = gulp.parallel(compileGeneralSCSS, compilePublicSCSS, compileAdminSCSS, compileGeneralJS, compilePublicJS, compileAdminJS);
const watch = gulp.series(build, watchFiles);
gulp.task('zip', gulp.series(build, createZip));

gulp.task('build', build);
gulp.task('watch', watch);

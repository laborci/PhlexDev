var gulp = require('gulp');
var less = require('gulp-less');
var uglifycss = require('gulp-uglifycss');
var autoprefixer = require('gulp-autoprefixer');
var fs = require("fs");

var assetsPath = './application/Assets';
var assetRoots = fs.readdirSync(assetsPath);

gulp.task('default', function() {
    for(var i = 0; i<assetRoots.length; i++) {
        gulp.src(assetsPath+'/'+assetRoots[i]+'/style/**/*.main.less')
           .pipe(less())
           .pipe(autoprefixer({
               browsers: ['last 2 versions'],
               cascade: false
           }))
           .pipe(uglifycss())
           .pipe(gulp.dest('./public/'+assetRoots[i]+'/css'));
    }
});
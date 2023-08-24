const gulp = require("gulp");
const zip = require("gulp-zip");
const del = require("del");

gulp.task("clean", () => {
  return del(["languages", "bundled"]);
});

function bundle() {
  return gulp.src(["**/*", "!node_modules/**", "!src/**", "!zip/**", "!composer-lock.json", "!composer.json", "!bundled/**", "!gulpfile.js", "!package.json", "!package-lock.json", "!webpack.config.js", "!.gitignore"]).pipe(gulp.dest("bundled/count-post-reactions"));
}

exports.bundle = bundle;

exports.zip = () => {
  return gulp.src(["bundled/**"]).pipe(zip("count-post-reactions.zip")).pipe(gulp.dest("zip"));
};

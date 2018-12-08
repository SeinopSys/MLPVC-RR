/* eslint-env node */
/* eslint no-console:0 */
(function() {

	"use strict";

	const
		chalk = require('chalk'),
		gulp = require('gulp'),
		plumber = require('gulp-plumber'),
		sass = require('gulp-sass'),
		autoprefixer = require('gulp-autoprefixer'),
		cleanCss = require('gulp-clean-css'),
		uglify = require('gulp-uglify'),
		babel = require('gulp-babel'),
		cached = require('gulp-cached'),
		rename = require('gulp-rename'),
		del = require('del'),
		fs = require('fs'),
		workingDir = __dirname;

	require('dotenv').load();
	const isProd = process.env.PRODUCTION === 'true';

	class Logger {
		constructor(prompt) {
			this.prefix = `[${chalk.blue(prompt)}] `;
		}

		log(message) {
			console.log(this.prefix + message);
		}

		error(message) {
			if (typeof message === 'string'){
				message = message.trim()
					.replace(/[/\\]?public/, '');
				console.error(this.prefix + 'Error in ' + message);
			}
			else console.log(JSON.stringify(message, null, '4'));
		}
	}

	const appendMinSuffix = () => rename((path) => { path.extname = `.min${path.extname}` });

	const clean = () => del(['public/js', 'public/css']);

	const lockfilePath = process.env.NPM_BUILD_LOCK_FILE_PATH;
	const lock = done => {
		fs.closeSync(fs.openSync(lockfilePath, 'a'));
		done();
	};
	const unlock = () => del([lockfilePath]);

	const createWatchers = done => {
		gulp.watch(JSWatchArray, { debounceDelay: 2000 }, gulp.series('js'));
		JSL.log('File watcher active');
		gulp.watch(SASSWatchArray, { debounceDelay: 2000 }, gulp.series('scss'));
		SASSL.log('File watcher active');
		done();
	};

	let SASSL = new Logger('scss'),
		SASSWatchArray = ['assets/scss/*.scss', 'assets/scss/**/*.scss'];
	gulp.task('scss', () => {
		let pipe = gulp.src(SASSWatchArray)
			.pipe(plumber(function(err) {
				SASSL.error(err.relativePath + '\n' + ' line ' + err.line + ': ' + err.messageOriginal);
				this.emit('end');
			}))
			.pipe(sass({
				outputStyle: 'expanded',
				errLogToConsole: true,
			}))
			.pipe(autoprefixer({
				browsers: ['last 2 versions', 'not ie <= 11'],
			}));
		if (isProd)
			pipe = pipe.pipe(cleanCss({
				processImport: false,
				compatibility: '-units.pc,-units.pt'
			}));
		return pipe
			.pipe(appendMinSuffix())
			.pipe(gulp.dest('public/css'));
	});

	let JSL = new Logger('js'),
		JSWatchArray = [
			'assets/js/*.js',
			'assets/js/**/*.js',
			'assets/js/*.jsx',
			'assets/js/**/*.jsx'
		];
	gulp.task('js', () => {
		let pipe = gulp.src(JSWatchArray)
			.pipe(cached('js', { optimizeMemory: true }))
			.pipe(plumber(function(err) {
				err =
					err.fileName
						? err.fileName.replace(workingDir, '') + '\n  line ' + (
						err._babel === true
							? err.loc.line
							: (err.lineNumber || '?')
					) + ': ' + err.message.replace(/^[/\\]/, '')
						.replace(err.fileName.replace(/\\/g, '/') + ': ', '')
						.replace(/\(\d+(:\d+)?\)$/, '')
						: err;
				JSL.error(err);
				this.emit('end');
			}))
			.pipe(babel({
				presets: ['@babel/env', '@babel/react'],
				plugins: [
					'@babel/plugin-transform-react-jsx',
					'@babel/plugin-proposal-object-rest-spread',
					'@babel/plugin-proposal-private-methods',
					'@babel/plugin-proposal-class-properties',
				]
			}));
		if (isProd)
			pipe = pipe.pipe(uglify({
				output: {
					comments: function(_, comment) {
						return /^!/m.test(comment.value)
					},
				},
			}));
		return pipe
			.pipe(appendMinSuffix())
			.pipe(gulp.dest('public/js'));
	});

	gulp.task('default', gulp.series(lock, clean, 'js', 'scss', unlock));

	gulp.task('watch', gulp.series('default', createWatchers));

})();

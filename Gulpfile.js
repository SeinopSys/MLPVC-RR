// jshint ignore: start
var cl = console.log;
console.log = console.writeLine = function () {
	var args = [].slice.call(arguments);
	if (args.length && /^(\[\d{2}:\d{2}:\d{2}]|Using|Starting|Finished)/.test(args[0]))
		return;
	return cl.apply(console, args);
};
var stdoutw = process.stdout.write;
process.stdout.write = console.write = function(str){
	var out = [].slice.call(arguments).join(' ');
	if (/\[.*\d.*]/g.test(out)) return;
	stdoutw.call(process.stdout, out);
};

var _sep = '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~',
	toRun = process.argv.slice(2).slice(-1)[0] || 'default'; // Works only if task name is the last param
console.writeLine('Gulp process awoken to run "'+toRun+'". It still appears to be tired.');
var require_list = ['gulp'];
if (['js','sass','md','default'].indexOf(toRun) !== -1){
	require_list.push.apply(require_list, [
		'gulp-newer',
		'gulp-sourcemaps',
		'gulp-rename',
		'gulp-plumber',
		'gulp-util',
	]);

	if (toRun === 'css' || toRun === 'default')
		require_list.push.apply(require_list, [
			'gulp-sass',
			'gulp-autoprefixer',
			'gulp-minify-css',
		]);
	if (toRun === 'js' || toRun === 'default')
		require_list.push.apply(require_list, [
			'gulp-uglify',
		]);
	if (toRun === 'md' || toRun === 'default')
		require_list.push.apply(require_list, [
			'gulp-markdown',
			'gulp-dom',
		]);
}
else if (toRun === 'pgsort')
	require_list.push('fs');
console.write('> *yaaawn*');
for (var i= 0,l=require_list.length; i<l; i++){
	var v = require_list[i];
	global[v.replace(/^gulp-([a-z]+).*$/, '$1')] = require(v);
	console.write(' '+v);
}
console.writeLine("\n> Huh? What? I'm pancake!   ...I-I mean, awake.\n"+_sep);

var workingDir = __dirname;

function Personality(prompt, onerror){
	if (typeof onerror !== 'object' || typeof onerror.length !== 'number' )
		onerror = false;
	var $p = '['+prompt+'] ';
	this.log = function(message){
		console.writeLine($p+message);
	};
	var getErrorMessage = function(){
		return onerror[Math.floor(Math.random()*onerror.length)];
	};
	this.error = function(message){
		if (typeof message === 'string') message = message.trim();
		else console.log(message);
		console.error((onerror?$p+getErrorMessage()+'\n':'')+$p+message);
	};
	return this;
}

var Flutters = new Personality(
	'sass',
	[
		"I don't mean to interrupt, but I found a tiny little issue",
		"This doesn't seem good",
		"Ouch",
	]
);
gulp.task('sass', function() {
	var DEST = 'www/css';
	gulp.src('www/sass/*.scss')
		.pipe(plumber(function(err){
			Flutters.error(err.messageFormatted || err);
			this.emit('end');
		}))
		.pipe(newer(DEST))
		.pipe(sourcemaps.init())
			.pipe(sass({
				outputStyle: 'expanded',
				errLogToConsole: true,
			}))
			.pipe(autoprefixer('last 2 version'))
			.pipe(rename({suffix: '.min' }))
			.pipe(minify({
				processImport: false,
				compatibility: '-units.pc,-units.pt'
			}))
		.pipe(sourcemaps.write('.', {
			includeContent: false,
			sourceRoot: '/sass',
		}))
		.pipe(gulp.dest(DEST));
});

var Dashie = new Personality(
	'js',
	[
		'OH COME ON!',
		'Not this again!',
		'Why does it have to be me?',
		"This isn't fun at all",
		"...seriously?",
	]
);
gulp.task('js', function(){
	var DEST = 'www/js';
	gulp.src(['www/js/*.js', '!www/js/*.min.js'])
		.pipe(plumber(function(err){
			err =
				err.fileName
				? err.fileName.replace(workingDir,'')+'\n  line '+err.lineNumber+': '+err.message.replace(/^[\/\\]/,'').replace(err.fileName+': ','')
				: err;
			Dashie.error(err);
			this.emit('end');
		}))
		.pipe(newer(DEST))
		.pipe(sourcemaps.init())
			.pipe(uglify({
				preserveComments: function(_, comment){ return /^!/m.test(comment.value) },
			}))
			.pipe(rename({suffix: '.min' }))
		.pipe(sourcemaps.write('.', {
			includeContent: false,
			sourceRoot: '/js',
		}))
		.pipe(gulp.dest(DEST));
});

var AJ = new Personality(
	'md',
	[
		'Awe, shucks!',
		'Stay calm sugarcube',
		'Ah seem to have a lil\' problem',
	]
);
gulp.task('md', function(){
	var DEST = 'www/views';
	gulp.src('README.md')
		.pipe(plumber(function(err){
			AJ.error(err);
			this.emit('end');
		}))
		.pipe(newer(DEST))
		.pipe(markdown())
		.pipe(dom(function(){
			var document = this,
				el = document.getElementById('what-s-this-site-'),
				newElements = '<section class="'+el.id+'">'+el.outerHTML;

			while (el.nextElementSibling !== null && el.nextElementSibling.id !== 'contributing'){
				var next = el.nextElementSibling;
				if (next.nodeName.toLowerCase() == 'h2')
					newElements += '</section><section class="'+next.id+'">';
				newElements += next.outerHTML;
				el = next;
			}

			return newElements+'\n';
		}))
		.pipe(rename('about.html'))
		.pipe(gulp.dest(DEST));
});

var Rarity = new Personality('pgsort', ['This is the WORST. POSSIBLE. THING!']),
	parseRow = function(r){
		var match = r.match(/VALUES \((\d+)(?:, (\d+|NULL))?[, )]/);
		if (!match)
			return [];
		return [match[1], match[2]];
	};
gulp.task('pgsort', function(){
	try {
		fs.readdir('./setup', function(err, dir){
			if (err) throw err;

			var i = 0;
			while (i < dir.length){
				if (!/\.pg\.sql$/.test(dir[i])){
					dir.splice(i, 1);
					continue;
				}
				i++;
			}

			for (i = 0; i<dir.length; i++)
				(function(fpath){
					fs.readFile(fpath, 'utf8', function(err, data){
						if (err) throw err;
						var test = /INSERT INTO "?([a-z_\-]+)"?\s*VALUES\s*\((\d+),[\s\S]+?;(?:\r|\r\n|\n)/g;
						if (!test.test(data))
							return;
						var Tables = {},
							TableCounters = {};
						data.replace(test,function(row,table){
							if (typeof Tables[table] !== 'object')
								Tables[table] = [];
							Tables[table].push(row);
							TableCounters[table] = 0;
							return row;
						});

						for (var j = 0, k = Object.keys(Tables), l = k.length; j<l; j++){
							var table = k[j];
							Tables[table].sort(function(a,b){
								a = parseRow(a);
								b = parseRow(b);

								var ix = 0;
								if (a[0] === b[0] && !isNaN(a[1]) && !isNaN(b[1]))
									ix++;

								a[ix] = parseInt(a[ix], 10);
								b[ix] = parseInt(b[ix], 10);

								return a[ix] > b[ix] ? 1 : (a[ix] < b[ix] ? -1 : 0);
							})
						}
						data = data.replace(test,function(row,table){
							return Tables[table][TableCounters[table]++];
						});
						data = data.replace(/;(?:\r|\r\n|\n)INSERT INTO "?([a-z_\-]+)"?\s+VALUES\s+/g,',\n');
						data = data.replace(/((?:\r|\r\n|\n)\s*(?:\r|\r\n|\n)INSERT INTO "?([a-z_\-]+)"?\s*VALUES)\s*\(/g,'$1\n(');
						data = data.replace(/\r\n?/g,'\n');

						fs.writeFile(fpath, data, function(err){
							if (err) throw err;
						});
					});
				})('./setup/'+dir[i]);
		});
	}
	catch(err){
		Rarity.error(err);
		this.emit('end');
	}
});

gulp.task('default', ['js', 'sass', 'md'], function(){
	gulp.watch(['www/js/*.js', '!www/js/*.min.js'], {debounceDelay: 2000}, ['js']);
	Dashie.log("I got my eyes on you, JavaScript files!");
	gulp.watch('www/sass/*.scss', {debounceDelay: 2000}, ['sass']);
	Flutters.log("SCSS files, do you mind if I, um, watch over you for a bit?");
	gulp.watch('README.md', {debounceDelay: 2000}, ['md']);
	AJ.log("Readme markdown file is under my radar, sugarcube");
	Rarity.log("PostgreSQL dump sorting should now be run manually!");
});

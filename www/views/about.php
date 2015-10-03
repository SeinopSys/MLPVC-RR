<div id="content">
	<img src="/img/logo.svg" alt="MLP Vector Club Requests &amp; Reservations Logo">
	<h1>MLP Vector Club Requests &amp; Reservations</h1>
	<p>An automated system for handling requests &amp; reservations, made for <a href="http://mlp-vectorclub.deviantart.com/">MLP-VectorClub</a></p>
	<section>
		<h2>What's this site?</h2>
		<div>
			<p>This website is a new, automatic way to process and store the requests &amp; reservations users want to make. It's that simple.</p>
			<p>In the past, the management of comments under journals was done manually. Because of this, there had to be a person who checks those comments, evaluates them, then updates the journal accordingly. This took time, sometimes, longer time than it should have taken. The group's staff consists of busy people, and we can't expect them to consantly monitor new incoming comments. But, with the help of this website, new entries can be submitted and added to a list, just like the journals, automatically, without having to have someone do this monotonous task.</p>
		</div>
	</section>
	<section>
		<h2>Why does the version number look so... <em>random</em>?</h2>
		<div>
			<p>This website's complete codebase is <a href="<?=GITHUB_URL?>">available for anyone to see on GitHub</a>. The version number is the first few characters of the latest commit's ID. In this case, a commit is basically an update to the site. Whenever a new update is applied, the version number changes automatically.</p>
		</div>
	</section>
	<section>
		<h2>Attributions</h2>
		<div>
			<p><strong>Used libraries &amp; icons include:</strong> <a href="http://jquery.com/">jQuery</a>, <a href="http://qtip2.com/">qTip<sup>2</sup></a>, <a href="https://twitter.github.io/typeahead.js/">typeahead.js</a>, <a href="https://github.com/RubaXa/Sortable">Sortable</a>, <a href="https://github.com/joshcam/PHP-MySQLi-Database-Class">MysqliDb</a>, <a href="https://github.com/cbschuld/Browser.php">Browser.php</a>, <a href="http://www.typicons.com/">Typicons</a>, <a href="https://www.npmjs.com/package/uglify-js">Uglify-js</a>, <a href="http://sass-lang.com/">SASS</a></p>
			<p><strong>Header font:</strong> <a href="http://www.mattyhex.net/CMR/">Celestia Medium Redux</a></p>
			<p><strong>DeviantArt logo</strong> <em>(used on profile pages)</em> &copy; <a href="http://www.deviantart.com/">DeviantArt</a></p>
			<p><strong>Application logo</strong> based on <a href="http://pirill-poveniy.deviantart.com/art/Collab-Christmas-Vector-of-the-MLP-VC-Mascot-503196118">Christmas Vector of the MLP-VC Mascot</a> by the following artists:</p>
			<ul>
				<li><a href="http://pirill-poveniy.deviantart.com/">Pirill-Poveniy</a></li>
				<li><a href="http://thediscorded.deviantart.com/">thediscorded</a></li>
				<li><a href="http://masemj.deviantart.com/">masemj</a></li>
				<li><a href="http://ambassad0r.deviantart.com/">Ambassad0r</a> <em>(idea)</em></li>
			</ul>
			<p><strong>Pre-ban dialog illustration (<a href="/img/pre-ban.svg">direct link</a>):</strong> <a href="http://synthrid.deviantart.com/art/Twilight-What-Have-I-Done-355177596">Twilight - What Have I Done?</a> by <a href="http://synthrid.deviantart.com/">Synthrid</a> <em>(edited to use daylight colors)</em></p>
			<p><strong>Post-ban dialog illustration (<a href="/img/post-ban.svg">direct link</a>):</strong> <a href="http://sairoch.deviantart.com/art/Sad-Twilight-Sparkle-354710611">Sad Twilight Sparkle</a> by <a href="http://sairoch.deviantart.com/">Sairoch</a> <em>(edited to use daylight colors)</em></p>
			<p><strong>Extrenal link icon</strong> (licensed GPL) taken from <a href="https://commons.wikimedia.org/wiki/File:Icon_External_Link.svg">Wikimedia Commons</a></p>
			<p><strong>Browser logos</strong> (used on user pages):</p>
			<ul>
				<li><a href="http://fav.me/d8uhefy">Edge</a> by <a href="https://furrgroup.deviantart.com/">furrgroup</a></li>
				<li><a href="http://fav.me/d4b6f4v">Firefox</a> by <a href="https://noreasontohope.deviantart.com/">NoReasonToHope</a></li>
				<li><a href="http://fav.me/d523s3y">Google Chrome</a> by <a href="https://he4rtofcourage.deviantart.com/">he4rtofcourage</a></li>
				<li><a href="http://fav.me/d5ktmlx">Internet Explorer</a> by <a href="https://mcsadat.deviantart.com/">McSadat</a></li>
				<li><a href="http://fav.me/d52qnaw">Opera</a> by <a href="https://parallaxmlp.deviantart.com/">ParallaxMLP</a></li>
				<li><a href="http://fav.me/d530knp">Safari</a> by <a href="https://parallaxmlp.deviantart.com/">ParallaxMLP</a></li>
				<li><a href="https://github.com/alrra/browser-logos/blob/1ec2af8325ffb8b3f2c2834da84870341a376efd/vivaldi/vivaldi_128x128.png">Vivaldi</a> from <a href="https://github.com/alrra/browser-logos">alrra/browser-logos</a></li>
			</ul>
			<p><strong>Coding and design</strong> by <a href="http://djdavid98.hu">DJDavid98</a></p>
			<p class="ramnode"><a href="https://clientarea.ramnode.com/aff.php?aff=2648"><img src="https://www.ramnode.com/images/banners/affbannerlightnewlogoblack.png" alt="high performance ssd vps""></a></p>
		</div>
	</section>
<? if (PERM('inspector')){ ?>
	<section>
		<h2><a href="/users"><span class="typcn typcn-arrow-back"></span>Linked users</a></h2>
		<p><em>This section has been moved to its own page.</em></p>
	</section>
	<section class="browsers">
		<h2>Most popular browsers</h2>
		<table>
			<thead><th>#</th><th>Name & Version</th><th>Sessions</th></thead>
			<tbody><?php
			$Data = $Database->rawQuery(
				"SELECT
					@name := browser_name as `name`,
					@ver := browser_ver as `ver`,
					CONCAT(browser_name, ' ', browser_ver) as browser,
					(
						SELECT COUNT(*)
						FROM sessions
						WHERE browser_name = @name && browser_ver = @ver
					) as users
				FROM `sessions`
				GROUP BY browser
				ORDER BY users DESC, browser_name, browser_ver DESC");
			$i = 0;
			$last = 0;
			foreach ($Data as $r){
				$s = $r['users'] !== 1 ? 's' : '';
				$ordering = '~';
				if ($last !== $r['users']){
					$last = $r['users'];
					$i++;
					$ordering = $i;
				}
				echo "<tr><td><strong>$ordering</strong></td><td>{$r['browser']}</td><td class='align-center'>{$r['users']}</td></tr>";
			} ?></tbody>
		</table>
	</section>
<?  } ?>
</div>

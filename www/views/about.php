<div id=content>
	<img src="<?=djpth('img>logo.png')?>">
	<h1>MLP Vector Club Requests & Reservations</h1>
	<p>An automated system for handling requests & reservations, made for MLP-VectorClub</p>
	<section>
		<h2>What's this site?</h2>
		<div>
			<p>This website is (would be) a new, automatic way to process and store the requests & reservations users want to make. It's that simple.</p>
		</div>
	</section>
	<section>
		<h2>Okay, but... what's the point?</h2>
		<div>
			<p>The management of comments under journals is currently done manually. Because of this, there has to be a person who checks those comments, evaluates them, then updates the journal accordingly. This takes time, usually, a long time. The group's staff consists of busy people, and we can't expect them to consantly monitor new incoming comments. But!</p>
			<p>Through this website, new entries could be submitted and added to a list, just like the journals, automatically, without having to have someone doing this monotonous task.</p>
		</div>
	</section>
	<section>
		<h2>Roll the credits, please!</h2>
		<div>
			<p><strong>Coding, design, hosting:</strong> <?=$DevLink?></p>
			<p><strong>Used libraries &amp; icons include:</strong> <a href="http://jquery.com/">jQuery</a>, <a href="https://github.com/joshcam/PHP-MySQLi-Database-Class">MysqliDb</a>, <a href="http://www.typicons.com/">Typicons</a></p>
			<p><strong>Header font:</strong> <a href="http://www.mattyhex.net/CMR/">Celestia Medium Redux</a></p>
			<p><strong>Application logo</strong> based on the MLP-VectorClub logo and <a href="http://djdavid98.deviantart.com/art/Rainbow-Dash-standing-S03E07-468486614">Rainbow Dash standing (S03E07)</a> by <?=$DevLink?></p>
		</div>
	</section>
<?php
	$Users = $Database->rawQuery('SELECT * FROM users WHERE role != \'developer\' ORDER BY username');
	if (!empty($Users)){
?>
	<section>
		<h2>Linked users</h2>
		<div>
			<p>Here's a grouped list of all the people who've been added to the site's database so far.</p>
<?php
		PERM(false);
		$Arranged = array();
		foreach ($Users as $u){
			if (!isset($Arranged[$u['role']])) $Arranged[$u['role']] = array();

			$Arranged[$u['role']][] = $u;
		}
		foreach ($Arranged as $group)
			usort($group,function($a, $b){
				global $ROLES;

				$aIndex = array_search($a, $ROLES);
				$bIndex = array_search($b, $ROLES);

				return $aIndex < $bIndex ? 1 : ($aIndex > $bIndex ? -1 : 0);
			});

		foreach ($Arranged as $role => $users){
			$s = count($users) !== 1 ? 's' : '';
			$usersStr = array();
			foreach ($users as $u)
				$usersStr[] = da_link($u, TEXT_ONLY);
			$usersStr = implode(', ', $usersStr);
			global $ROLES_ASSOC;
			echo <<<HTML

				<p><strong>{$ROLES_ASSOC[$role]}$s:</strong> $usersStr</p>
HTML;
		} ?>
		</div>
	</section>
<?php } ?>
</div>
<div id=sidebar>
<?php include "views/sidebar.php"; ?>
</div>
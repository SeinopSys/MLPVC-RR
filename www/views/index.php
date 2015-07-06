<?php if ($do === 'da-auth' && isset($err)){ ?>
<div class="notice fail align-center">
	<p>There was a(n) <strong><?=$err?></strong> error while trying to authenticate with deviantArt<?=isset($OAUTH_RESPONSE[$err])?"; {$OAUTH_RESPONSE[$err]}":'.'?></p>
<?php   if (!empty($errdesc)){ ?>
	<p>Additional details: <?=$errdesc?></p>
<?php   } ?>
</div>
<?php } ?>
<div id=content>
<?php if(!empty($CurrentEpisode)){ ?>
	<h1><?=format_episode_title($CurrentEpisode)?></h1>
	<p>Vector Requests & Reservations</p>
<?php if (PERM('episodes.manage')){ ?>
	<p class=align-center><em>Episode added by <?=profile_link(get_user($CurrentEpisode['posted_by'])).' '.timetag($CurrentEpisode['posted'])?></em></p>
<?php } ?>
<?php
	$_VIDEO_PROVIDER_NAMES = array(
		'yt' => 'YouTube',
		'dm' => 'Dailymotion',
	);
	$Videos = $Database
		->orderBy('provider', 'ASC')
		->whereEp($CurrentEpisode['season'],$CurrentEpisode['episode'])
		->get('episodes__videos');
	if (!empty($Videos)){
		require_once "includes/Video.php";
		$FirstVid = $Videos[0];
		$embed = Video::get_embed($FirstVid['id'], $FirstVid['provider']);

		if (!empty($Videos[1])){
			$SecondVid = $Videos[1];
			$url = Video::get_embed($SecondVid['id'], $SecondVid['provider'], Video::URL_ONLY);
			echo "<p class=align-center style=margin-bottom:5px>Watch the episode below or on <a href='$url' target=_blank>{$_VIDEO_PROVIDER_NAMES[$SecondVid['provider']]}</a></em></p>";
		}
		echo "<div class=responsive-embed>$embed</div>";
	} ?>
	<div class="notice info">
		<label>What Vector Reservations Are</label>
		<p>People usually get excited whenever a new episode comes out, and start making vectors of any pose/object/etc. that they found hilarious/interesting enough. It often results in various people unnecessarily doing the very same thing. Vector Reservations can help organize our efforts by listing who's working on what and to reduce the number of duplicates.</p>
	</div>
	<div class="notice caution rules">
		<label>Reservation Rules</label>
		<ol>
			<li>You MUST have an image to make a reservation! For the best quality, get your references from the episode in 1080p.</li>
			<li>Making a reservation does NOT forbid other people from working on a pose anyway. It is only information that you are working on it, so other people can coordinate to avoid doing the same thing twice.</li>
			<li>There are no time limits, but remember that the longer you wait, the greater the chance that someone might take your pose anyway. It's generally advised to finish your reservations before a new episode comes out.</li>
			<li>The current limit for reservations are 4 at a time. You can reserve more once you've completed the previous reservation(s).</li>
			<li>Please remember that <strong>you have to be a member of the group in order to make a reservation</strong>. The idea is to add the finished vector to our gallery, so it has to meet all of our quality requirements.</li>
		</ol>
	</div>
<?php   if (PERM('episodes.manage')){ ?>
	<p class=align-center>
		<button id=export class="typcn typcn-export large blue">Export posts in journal format</button>
	</p>
<?php   }
		echo reservations_render($Reservations);
		echo requests_render($Requests); ?>
	<script>var SEASON = <?=$CurrentEpisode['season']?>, EPISODE = <?=$CurrentEpisode['episode']?>;</script>
<?php
	} else { ?>
	<h1>There's nothing here yet...</h1>
	<p>...but there will be!</p>

<?php   if (PERM('episodes.manage')){ ?>
	<div class="notice info">
		<label>No episodes found</label>
		<p>To make the site functional, you must add an episode to the database first. Head on over to the <a href="/episodes">Episodes</a> page and add one now!</p>
	</div>
<?php   }
	} ?>
</div>
<div id=sidebar>
<?php
	if (!empty($CurrentEpisode)){
		$CurrentEpisode['willair'] = gmdate('c', strtotime('+'.(!$CurrentEpisode['twoparter'] ? '30' : '60').' minutes',strtotime($CurrentEpisode['airs']))); ?>
	<section id=voting>
		<h2>Episode voting</h2>
		<?=get_episode_voting($CurrentEpisode)?>
	</section>
<?php
	}
	include "views/sidebar.php"; ?>
</div>
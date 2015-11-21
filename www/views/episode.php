<?php

	if ($do === 'da-auth' && isset($err)){
		echo Notice('fail',"There was a(n) <strong>$err</strong> error while trying to authenticate with DeviantArt".(isset($OAUTH_RESPONSE[$err])?"; {$OAUTH_RESPONSE[$err]}":'.').(!empty($errdesc)?"\n\nAdditional details: $errdesc":''),true) ?>
<script>try{history.replaceState('',{},'/')}catch(e){}</script>
<?  } ?>
<div id="content">
<?  if (!empty($CurrentEpisode)){
		$isMovie = $CurrentEpisode['season'] === 0;?>
	<h1><?=format_episode_title($CurrentEpisode)?></h1>
	<p>Vector Requests & Reservations</p>
<?php   if (PERM('inspector')){ ?>
	<p class="align-center"><em><?=$isMovie?'Movie':'Episode'?> added by <?=profile_link(get_user($CurrentEpisode['posted_by'])).' '.timetag($CurrentEpisode['posted'])?></em></p>
<?php   }
	echo render_ep_video($CurrentEpisode); ?>
	<section class="about-res">
		<h2>What Vector Reservations Are</h2>
		<p>People usually get excited whenever a new <?=$isMovie?'movie':'episode'?> comes out, and start making vectors of any pose/object/etc. that they found hilarious/interesting enough. It often results in various people unnecessarily doing the very same thing. Vector Reservations can help organize our efforts by listing who's working on what and to reduce the number of duplicates.</p>
	</section>
	<section class="rules">
		<h2>Reservation Rules</h2>
		<ol>
			<li>You MUST have an image to make a reservation! For the best quality, get your references from the <?=$isMovie?'movie':'episode'?> in 1080p.</li>
			<li>Making a reservation does NOT forbid other people from working on a pose anyway. It is only information that you are working on it, so other people can coordinate to avoid doing the same thing twice.</li>
			<li>There are no time limits, but remember that the longer you wait, the greater the chance that someone might take your pose anyway. It's generally advised to finish your reservations before a new episode comes out.</li>
			<li>The current limit for reservations are 4 at a time. You can reserve more once you've completed the previous reservation(s).</li>
			<li>Please remember that <strong>you have to be a member of the group in order to make a reservation</strong>. The idea is to add the finished vector to our gallery, so it has to meet all of our quality requirements.</li>
		</ol>
	</section>
<?php   $EpTag = $CGDb->where('name',"s{$CurrentEpisode['season']}e{$CurrentEpisode['episode']}")->getOne('tags');
		if (!empty($EpTag)){
			$TaggedAppearances = $CGDb->rawQuery(
				"SELECT p.id, p.label
				FROM tagged t
				LEFT JOIN appearances p ON t.ponyid = p.id
				WHERE t.tid = {$EpTag['tid']}");

			if (!empty($TaggedAppearances)){ ?>
	<section class="appearances">
		<h2>Related <a href="/colorguide"><?=$Color?> Guide</a> <?=plur('page', count($TaggedAppearances))?></h2>
		<p><?php
				$HTML = '';
				foreach ($TaggedAppearances as $p)
					$HTML .= "<a href='/colorguide/appearance/{$p['id']}'>{$p['label']}</a>, ";
				echo rtrim($HTML,', ');
		?></p>
	</section>
<?php		}
		}
		if (PERM('inspector')){ ?>
	<section class="admin">
		<h2>Administration area</h2>
		<p class="align-center">
			<button id="video" class="typcn typcn-video large darkblue">Set video links</button>
		</p>
	</section>
<?php   }
		echo reservations_render($Reservations);
		echo requests_render($Requests); ?>
	<script>var SEASON = <?=$CurrentEpisode['season']?>, EPISODE = <?=$CurrentEpisode['episode']?>;</script>
<?php
	} else { ?>
	<h1>There's nothing here yet&hellip;</h1>
	<p>&hellip;but there will be!</p>

<?php   if (PERM('inspector'))
			echo Notice('info','No episodes found',"To make the site functional, you must add an episode to the database first. Head on over to the <a href='/episodes'>Episodes</a> page and add one now!");
	} ?>
</div>

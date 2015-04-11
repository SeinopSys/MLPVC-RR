<?php
	if ($do === 'da-auth' && isset($_GET['error'])){
		$err = $_GET['error'];
?>
<div class="notice fail align-center">
	<p>There was a(n) <strong><?=$err?></strong> error while trying to authenticate with deviantArt<?=isset($OAUTH_RESPONSE[$err])?"; {$OAUTH_RESPONSE[$err]}":'.'?></p>
<?php   if (!empty($_GET['error_description'])){ ?>
	<p>Additional details: <?=$_GET['error_description']?></p>
<?php   } ?>
</div>
<?php } ?>
<div class="content grid-70">
<?php if(!empty($CurrentEpisode)){ ?>
	<h1><?=format_episode_title($CurrentEpisode)?></h1>
	<p>Vector Requests & Reservations</p>
	<div class="notice info">
		<label>What Vector Reservations Are</label>
		<p>People usually get excited whenever a new episode comes out, and start making vectors of any pose/object/etc. that they found hilarious/interesting enough. It often results in various people unnecessarily doing the very same thing. Vector Reservations can help organize our efforts by listing who's working on what and to reduce the number of duplicates.</p>
	</div>
	<div class="notice caution rules">
		<label>Rules</label>
		<ol>
			<li>You MUST have an image to make a reservation! For the best quality, get your references from the episode in 1080p.</li>
			<li>Making a reservation does NOT forbid other people from working on a pose anyway. It is only information that you are working on it, so other people can coordinate to avoid doing the same thing twice.</li>
			<li>There are no time limits, but remember that the longer you wait, the greater the chance that someone might take your pose anyway. It's generally advised to finish your reservations before a new episode comes out.</li>
			<li>The are no limits on the number of reservations you can make, but you need to remember about your own limits and what rule #3 says. It's generally advised not to take more than 3 reservations at a time.</li>
			<li>Please remember that you have to be a member of the group in order to make a reservation. The idea is to add the finished vector to the gallery, so it has to meet all of our quality requirements.</li>
		</ol>
	</div>

	<div class="notice info">
		<p>Below is some placeholder data added directly through the database, the interface for adding the entries below has yet to be made.</p>
	</div>

	<section id="reservations">
		<div class="unfinished">
			<h2>List of Reservations</h2>
			<ul><?php

			?></ul>
		</div>
		<div class="finished">
			<h2>Finished Reservations</h2>
			<ul><?php

			?></ul>
		</div>
	</section>
<?php   requests_render($Requests);
	} else { ?>
	<h1>No episode found</h1>
	<p>There are no episodes stored in the database</p>

<?php   if ($signedIn && !rankCompare($currentUser['role'],'inspector')){ ?>
	<div class="notice info">
		<p>To make the site functional, first, you'll need to add an episode to the database which will then be displayed here.</p>
		<p>There's no visual interface yet, so you're on your own for now, sorry :\</p>
	</div>
<?php   }
	} ?>
</div>
<div class="sidebar grid-30">
<?php include "views/sidebar.php"; ?>
</div>
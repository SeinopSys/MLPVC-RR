<?php
/**
 * @var $CurrentEpisode \DB\Episode
 * @var $NextEpisode    \DB\Episode
 * @var $PrevEpisode    \DB\Episode
 */

	if ($do === 'da-auth' && isset($err)){
		echo CoreUtils::Notice('fail',"There was a(n) <strong>$err</strong> error while trying to authenticate with DeviantArt".(isset(DeviantArt::$OAUTH_RESPONSE[$err])?'; '.DeviantArt::$OAUTH_RESPONSE[$err]:'.').(!empty($errdesc)?"\n\nAdditional details: $errdesc":''),true) ?>
<script>try{history.replaceState('',{},'/')}catch(e){}</script>
<?  } ?>
<div id="content">
<?  if (!empty($CurrentEpisode)){ ?>
	<div class="heading-wrap">
<?php   if (!empty($PrevEpisode)){
			$PrevEpisodeTitle = $PrevEpisode->formatTitle(AS_ARRAY, null, false); ?>
		<div class="prev-ep">
			<a href="<?=$PrevEpisode->formatURL()?>" class="ep-button btn typcn typcn-media-rewind"><span class="typcn typcn-media-rewind"></span><span class="id"><?=$PrevEpisodeTitle['id']?>: </span><?=CoreUtils::Cutoff(Episodes::RemoveTitlePrefix($PrevEpisodeTitle['title']),Episodes::TITLE_CUTOFF)?></a>
		</div>
<?php   }
		if (!empty($NextEpisode)){
			$NextEpisodeTitle = $NextEpisode->formatTitle(AS_ARRAY, null, false); ?>
		<div class="next-ep">
			<a href="<?=$NextEpisode->formatURL()?>" class="ep-button btn typcn typcn-media-fast-forward"><span class="id"><?=$NextEpisodeTitle['id']?>: </span><?=CoreUtils::Cutoff(Episodes::RemoveTitlePrefix($NextEpisodeTitle['title']),Episodes::TITLE_CUTOFF)?><span class="typcn typcn-media-fast-forward"></span></a>
		</div>
<?php   } ?>
		<div class="main">
			<h1><?=$heading?></h1>
			<p>Vector Requests & Reservations</p>
<?php   if (Permission::Sufficient('staff')){ ?>
			<p class="addedby"><em><?=$CurrentEpisode->isMovie?'Movie':'Episode'?> added by <?=Users::Get($CurrentEpisode->posted_by)->getProfileLink().' '.Time::Tag($CurrentEpisode->posted)?></em></p>
<?php   } ?>
		</div>
	</div>
	<?=Episodes::GetVideosHTML($CurrentEpisode)?>
	<section class="about-res">
		<h2>What Vector Reservations Are<?=Permission::Sufficient('staff')?'<button class="blue typcn typcn-pencil" id="edit-about_reservations">Edit</button>':''?></h2>
		<?=GlobalSettings::Get('about_reservations')?>
	</section>
	<section class="rules">
		<h2>Reservation Rules<?=Permission::Sufficient('staff')?'<button class="orange typcn typcn-pencil" id="edit-reservation_rules">Edit</button>':''?></h2>
		<?=GlobalSettings::Get('reservation_rules')?>
	</section>
<?php   echo Episodes::GetAppearancesSectionHTML($CurrentEpisode);
		if (Permission::Sufficient('staff')){ ?>
	<section class="admin">
		<h2>Administration area</h2>
		<p class="align-center">
			<button id="edit-ep" class="typcn typcn-pencil large darkblue">Metadata</button>
			<button id="video" class="typcn typcn-pencil large darkblue">Video links</button>
			<button id="cg-relations" class="typcn typcn-pencil large darkblue">Guide relations</button>
		</p>
	</section>
<?php   }
		echo Posts::GetReservationsSection(null,false,true);
		echo Posts::GetRequestsSection(null,false,true);
		$export = array(
			'SEASON' => $CurrentEpisode->season,
			'EPISODE' => $CurrentEpisode->episode,
		);
		if (Permission::Sufficient('developer'))
			$export['USERNAME_REGEX'] = $USERNAME_REGEX;
		if ($signedIn)
			$export['FULLSIZE_MATCH_REGEX'] = $FULLSIZE_MATCH_REGEX;
		CoreUtils::ExportVars($export);
	} else { ?>
	<h1>There's nothing here yet&hellip;</h1>
	<p>&hellip;but there will be!</p>

<?php   if (Permission::Sufficient('staff'))
			echo CoreUtils::Notice('info','No episodes found',"To make the site functional, you must add an episode to the database first. Head on over to the <a href='/episodes'>Episodes</a> page and add one now!");
	} ?>
</div>

<?  if (Permission::Sufficient('staff'))
		CoreUtils::ExportVars(array('EP_TITLE_REGEX' => $EP_TITLE_REGEX));

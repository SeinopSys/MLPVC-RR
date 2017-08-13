<?php
use App\Auth;
use App\CoreUtils;
use App\Episodes;
use App\GlobalSettings;
use App\Permission;
use App\Posts;
use App\Time;
use App\Models\Episode;

/** @var $CurrentEpisode Episode */
/** @var $NextEpisode    Episode */
/** @var $PrevEpisode    Episode */
/** @var $do string */
/** @var $heading string */ ?>
<div id="content">
<?  if (!empty($CurrentEpisode)){
		$reverseBtns = \App\UserPrefs::get('ep_revstepbtn');
		if ($reverseBtns){
			$tmp = $PrevEpisode;
			$PrevEpisode = $NextEpisode;
			$NextEpisode = $tmp;
			unset($tmp);
		} ?>
	<div class="heading-wrap">
		<div class="prev-ep"><?php
		if (!empty($PrevEpisode)){ ?>
			<div>
				<a href="<?=$PrevEpisode->toURL()?>" class="btn link ep-button typcn typcn-media-rewind"><span class="typcn typcn-media-rewind"></span><?=CoreUtils::cutoff(Episodes::shortenTitlePrefix($PrevEpisode->title),Episodes::TITLE_CUTOFF)?></a>
			</div>
<?php   }
		else echo '&nbsp;'; ?></div>
		<div class="main">
			<div>
				<h1><?=CoreUtils::escapeHTML($heading)?></h1>
				<p>Vector Requests & Reservations</p>
<?php   if (Permission::sufficient('staff')){ ?>
				<p class="addedby"><em><?=$CurrentEpisode->is_movie?'Movie':'Episode'?> added by <?=$CurrentEpisode->poster->toAnchor().' '.Time::tag($CurrentEpisode->posted)?></em></p>
<?php   } ?>
			</div>
		</div>
		<div class="next-ep"><?php
		if (!empty($NextEpisode)){ ?>
			<div>
				<a href="<?=$NextEpisode->toURL()?>" class="btn link ep-button typcn typcn-media-fast-forward"><?=CoreUtils::cutoff(Episodes::shortenTitlePrefix($NextEpisode->title),Episodes::TITLE_CUTOFF)?><span class="typcn typcn-media-fast-forward"></span></a>
			</div>
<?php   }
		else echo '&nbsp;'; ?></div>
	</div>
	<?=Episodes::getVideosHTML($CurrentEpisode)?>
	<section class="about-res">
		<h2>What Vector Reservations Are<?=Permission::sufficient('staff')?'<button class="blue typcn typcn-pencil" id="edit-about_reservations">Edit</button>':''?></h2>
		<?=GlobalSettings::get('about_reservations')?>
	</section>
	<section class="rules">
		<h2>Reservation Rules<?=Permission::sufficient('staff')?'<button class="orange typcn typcn-pencil" id="edit-reservation_rules">Edit</button>':''?></h2>
		<?=GlobalSettings::get('reservation_rules')?>
	</section>
<?php   if (!empty($CurrentEpisode->notes)){ ?>
	<section class="notes">
		<h2>Notes from the staff</h2>
		<pre><?=$CurrentEpisode->notes?></pre>
	</section>
<?php   }
		echo Episodes::getAppearancesSectionHTML($CurrentEpisode);
		if (Permission::sufficient('staff')){ ?>
	<section class="admin">
		<h2>Administration area</h2>
		<p class="align-center">
			<button id="edit-ep" class="typcn typcn-pencil large darkblue">Metadata</button>
			<button id="video" class="typcn typcn-pencil large darkblue">Video links</button>
			<button id="cg-relations" class="typcn typcn-pencil large darkblue">Guide relations</button>
		</p>
	</section>
<?php   }
		echo Posts::getReservationsSection(Posts::get($CurrentEpisode, ONLY_RESERVATIONS, Permission::sufficient('staff')),false,true);
		echo Posts::getRequestsSection(Posts::get($CurrentEpisode, ONLY_REQUESTS, Permission::sufficient('staff')),false,true);
		$export = [
			'SEASON' => $CurrentEpisode->season,
			'EPISODE' => $CurrentEpisode->episode,
		];
		if (Permission::sufficient('developer')){
			global $USERNAME_REGEX;
			$export['USERNAME_REGEX'] = $USERNAME_REGEX;
		}
		if (Auth::$signed_in){
			global $FULLSIZE_MATCH_REGEX;
			$export['FULLSIZE_MATCH_REGEX'] = $FULLSIZE_MATCH_REGEX;
		}
		echo CoreUtils::exportVars($export);
	} else { ?>
	<h1>There’s nothing here yet&hellip;</h1>
	<p>&hellip;but there will be!</p>

<?php   if (Permission::sufficient('staff'))
			echo CoreUtils::notice('info','No episodes found',"To make the site functional, you must add an episode to the database first. Head on over to the <a href='/episodes'>Episodes</a> page and add one now!");
	} ?>
</div>

<?  $exp = ['EpisodePage'=>true];
	if (Permission::sufficient('staff')){
		global $EP_TITLE_REGEX;
		$exp['EP_TITLE_REGEX'] = $EP_TITLE_REGEX;
	}
	echo CoreUtils::exportVars($exp);
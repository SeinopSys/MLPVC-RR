<div id="content">
<?php
	if (isset($MSG)){
		echo "<h1>$MSG</h1>";
		if (isset($SubMSG)) echo "<p>$SubMSG</p>";
	}
	else {
		echo User::GetAvatarWrap($User); ?>
	<h1><?=$User['name']?> <a class="da" title="Visit DeviantArt profile" href="<?=User::GetDALink($User,LINK_ONLY)?>"><?=str_replace(' fill="#FFF"','',file_get_contents(APPATH.'img/da-logo.svg'))?></a></h1>
	<p><?php
		echo "<span>{$User['rolelabel']}</span>";
		if ($canEdit){
			echo ' <button id="change-role" class="blue typcn typcn-spanner'.($User['role']==='ban'?' hidden':'').'" title="Change '.CoreUtils::Posess($User['name']).' group"></button>';
			$BanLabel = ($User['role']==='ban'?'Un-ban':'Ban').'ish';
			$Icon = $User['role']==='ban'?'world':'weather-night';
			if (Permission::Sufficient('inspector', $User['role']))
				$Icon .= ' hidden';
			echo ' <button id="ban-toggle" class="darkblue typcn typcn-'.$Icon.' '.strtolower($BanLabel).'" title="'."$BanLabel user".'"></button>';
		}
	?></p>
	<div class="details">
<?php

	$DevSection =
	$PublicSection =
	$PrivateSection =
	$VeryPrivateSection = '';

	if ($sameUser || Permission::Sufficient('inspector')){
		$you = $sameUser ? 'you' : 'the user';
		$DevSection = "<span class='typcn typcn-cog color-red' title='Visible to: developer'></span>";
		$PublicSection = "<span class='typcn typcn-world color-blue' title='Visible to: public'></span>";
		$PrivateSection = "<span class='typcn typcn-lock-closed' title='Visible to: $you & group administrators'></span>";
		$VeryPrivateSection = "<span class='typcn typcn-lock-closed color-green' title='Visible to: $you'></span>";
	}

	if (Permission::Sufficient('developer')){ ?>
		<section>
			<h2><?=$DevSection?>User ID:</h2>
			<span><?=$User['id']?></span>
		</section>
<?  }

	$cols = "id, season, episode, preview, label, posted";
	$PendingReservations = $Database->where('reserved_by', $User['id'])->where('deviation_id IS NULL')->get('reservations',null,$cols);
	$PendingRequestReservations = $Database->where('reserved_by', $User['id'])->where('deviation_id IS NULL')->get('requests',null,"$cols, reserved_at, true as rq");
	$TotalPending = count($PendingReservations)+count($PendingRequestReservations);
	$hasPending = $TotalPending > 0;
	if ((Permission::Sufficient('inspector') || $sameUser) && Permission::Sufficient('member', $User['role'])){
		$YouHave = ($sameUser?'You have':'This user has'); ?>
		<section class="pending-reservations">
			<h2><?=$PrivateSection?>Pending reservations</h2>
			<span><?="$YouHave ".($hasPending>0?"<strong>$TotalPending</strong>":'no')?> pending reservation<?php
		echo $TotalPending!==1?'s':'';
		if ($hasPending)
			echo " which ha".($TotalPending!==1?'ve':'s')."n't been marked as finished yet";
		echo ".";
		if ($sameUser)
			echo " Please keep in mind that the global limit is 4 at any given time. If you reach the limit, you can't reserve any more images until you finish or cancel some of your pending reservations.";
			?></span>
<?php
		if ($hasPending){
			$Posts = array_merge(
				Posts::GetReservationsSection($PendingReservations, RETURN_ARRANGED)['unfinished'],
				array_filter(array_values(Posts::GetRequestsSection($PendingRequestReservations, RETURN_ARRANGED)['unfinished']))
			);
			usort($Posts, function($a, $b){
				$a = strtotime($a['posted']);
				$b = strtotime($b['posted']);

				return -($a < $b ? -1 : ($a === $b ? 0 : 1));
			});
			$HTML = '';
			foreach ($Posts as $p){
				list($link,$page) = Posts::GetLink($p);
				$label = !empty($p['label']) ? "<span class='label'>{$p['label']}</span>" : '';
				$is_request = isset($p['rq']);
				$reservation_time_known = !empty($p['reserved_at']);
				$posted = Time::Tag($is_request && $reservation_time_known ? $p['reserved_at'] : $p['posted']);
				$PostedAction = $is_request && !$reservation_time_known ? 'Posted' : 'Reserved';

				$HTML .= <<<HTML
<li>
	<div class='image screencap'>
		<a href='$link'><img src='{$p['preview']}'></a>
	</div>
	$label
	<em>$PostedAction under <a href='$link'>$page</a> $posted</em>
	<div>
		<a href='$link' class='btn blue typcn typcn-arrow-forward'>View</a>
	</div>
</li>

HTML;
			}
			echo "<ul>$HTML</ul>";
		}
?>
		</section>
<?php   $cols = "id, season, episode, deviation_id as deviation";
		$AwaitingApproval = array_merge(
			$Database
				->where('reserved_by', $User['id'])
				->where('deviation_id IS NOT NULL')
				->where('"lock" IS NOT TRUE')
				->get('reservations',null,$cols),
			$Database
				->where('reserved_by', $User['id'])
				->where('deviation_id IS NOT NULL')
				->where('"lock" IS NOT TRUE')
				->get('requests',null,"$cols, true as rq")
		);
		$AwaitCount = count($AwaitingApproval);
		$them = $AwaitCount!==1?'them':'it'; ?>
		<section class="awaiting-approval">
			<h2><?=$PrivateSection?>Vectors waiting for approval</h2>
<?php   if ($sameUser){ ?>
			<p>After you finish an image and submit it to the group gallery, an inspector will check your vector and may ask you to fix some issues on your image, if any. After an image is accepted to the gallery, it can be marked as "approved", which gives it a green check mark, indicating that it's most likely free of any errors.</p>
<?php   } ?>
			<p><?="$YouHave ".(!$AwaitCount?'no':"<strong>$AwaitCount</strong>")?> image<?=$AwaitCount!==1?'s':''?> waiting to be submited to and/or approved by the group<?=!$AwaitCount?'.':(", listed below.".($sameUser?" We suggest that you submit $them to the group gallery at your earliest convenience to have $them spot-checked for any issues and added to the group gallery, making $them easier for others to find.":''))?></p>
<?php   if ($AwaitCount){ ?>
			<ul id="awaiting-deviations"><?
			foreach ($AwaitingApproval as $row){
				$deviation = DeviantArt::GetCachedSubmission($row['deviation']);
				$url = "http://{$deviation['provider']}/{$deviation['id']}";
				list($link,$page) = Posts::GetLink($row);
				$thing = isset($row['rq']) ? 'request' : 'reservation';
				$checkBtn = Permission::Sufficient('member') ? "\n\t\t<button class='green typcn typcn-tick check'>Check</button>" : '';

				echo <<<HTML
<li id="{$thing}-{$row['id']}">
	<div class="image deviation">
		<a href="$url" target="_blank">
			<img src="{$deviation['preview']}" alt="{$deviation['title']}">
		</a>
	</div>
	<span class="label"><a href="$url" target="_blank">{$deviation['title']}</a></span>
	<em>Posted under <a href='$link'>$page</a></em>
	<div>
		<a href='$link' class='btn blue typcn typcn-arrow-forward'>View</a>$checkBtn
	</div>
</li>
HTML;

			} ?></ul>
<?php   } ?>
		</section>
<?  } ?>
		<section class="bans">
			<h2><?=$PublicSection?>Banishment history</h2>
			<ul><?php
		$Actions = array('Banish','Un-banish');
		$Banishes = $Database
			->where('target', $User['id'])
			->join('log l',"l.reftype = 'banish' AND l.refid = b.entryid")
			->orderBy('l.timestamp')
			->get('log__banish b',null,"b.reason, l.initiator, l.timestamp, 0 as action");
		if (!empty($Banishes)){
			$Unbanishes = $Database
				->where('target', $User['id'])
				->join('log l',"l.reftype = 'un-banish' AND l.refid = b.entryid")
				->get('log__un-banish b',null,"b.reason, l.initiator, l.timestamp, 1 as action");
			if (!empty($Unbanishes)){
				$Banishes = array_merge($Banishes,$Unbanishes);
				usort($Banishes, function($a, $b){
					$a = strtotime($a['timestamp']);
					$b = strtotime($b['timestamp']);
					return $a > $b ? -1 : ($a < $b ? 1 : 0);
				});
				unset($Unbanishes);
			}

			$displayInitiator = Permission::Sufficient('inspector');

			foreach ($Banishes as $b){
				$initiator = $displayInitiator ? User::Get($b['initiator']) : null;
				$b['reason'] = htmlspecialchars($b['reason']);
				echo "<li class=".strtolower($Actions[$b['action']])."><blockquote>{$b['reason']}</blockquote> - ".(isset($initiator)?User::GetProfileLink($initiator).' ':'').Time::Tag($b['timestamp'])."</li>";
			}
		}
			?></ul>
		</section>
	</div>
	<div class="settings"><?php
		if ($sameUser || Permission::Sufficient('manager')){ ?>
		<section class="sessions">
			<h2><?=$PrivateSection?>Sessions</h2>
<?php       if (isset($CurrentSession) || !empty($Sessions)){ ?>
			<p>Below is a list of all the browsers <?=$sameUser?"you've":'this user has'?> logged in from.</p>
			<ul class="session-list"><?php
				if (isset($CurrentSession)) User::RenderSessionLi($CurrentSession,CURRENT);
				if (!empty($Sessions)){
					foreach ($Sessions as $s) User::RenderSessionLi($s);
				}
			?></ul>
			<p><button class="typcn typcn-arrow-back yellow" id="signout-everywhere">Sign out everywhere</button></p>
<?php       } else { ?>
			<p><?=$sameUser?'You are':'This user is'?>n't logged in anywhere.</p>
<?php       } ?>
		</section>
<?php   }
		if ($sameUser){ ?>
		<section>
			<h2><?=$VeryPrivateSection?>Unlink account</h2>
			<p>By unlinking your account you revoke this site's access to your account information. This will also log you out on every device where you're currently logged in. The next time you want to log in, you'll have to link your account again. This will not remove any of your <strong>public</strong> data from our site, it's still kept locally.</p>
	        <button id="unlink" class="orange typcn typcn-times">Unlink Account</button>
	    </section>
<?php   } ?></div>
<?php } ?>
</div>

<?php if ($canEdit){ ?>
<script>var ROLES = <?php
	$Echo = array();
	if ($canEdit){
		$_Roles = Permission::$ROLES_ASSOC;
		unset($_Roles['guest']);
		unset($_Roles['ban']);
		foreach ($_Roles as $name => $label){
			if (Permission::Insufficient($name, $currentUser['role']))
				continue;
			$Echo[$name] = $label;
		}
	}
	echo JSON::Encode($Echo);
?>;</script>
<?php }?>

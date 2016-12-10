<?php
use App\CGUtils;
use App\CoreUtils;
use App\Models\User;
use App\DeviantArt;
use App\Permission;
use App\Users;
use App\Appearances;
use App\ColorGroups;
use App\Tags; ?>
<div id="content">
	<div class="sprite-wrap"><?=Appearances::GetSpriteHTML($Appearance)?></div>
	<h1><?=CoreUtils::escapeHTML($heading)?></h1>
	<p>from the MLP-VectorClub <a href="/cg"><?=$Color?> Guide</a></p>

<?  if (Permission::Sufficient('staff')){ ?>
	<div class="notice warn align-center appearance-private-notice"<?=!empty($Appearance['private'])?'':' style="display:none"'?>><p><span class="typcn typcn-lock-closed"></span> <strong>This appearance is currently private (its colors are only visible to staff members)</strong></p></div>
<?php
	}

	$RenderPath = FSPATH."cg_render/{$Appearance['id']}.png";
	$FileModTime = '?t='.(file_exists($RenderPath) ? filemtime($RenderPath) : time()); ?>
	<div id="p<?=$Appearance['id']?>">
		<div class='align-center'>
			<a class='darkblue btn typcn typcn-image' href='/cg/v/<?="{$Appearance['id']}.png$FileModTime"?>' target='_blank'>View as PNG</a>
			<button class='getswatch typcn typcn-brush teal'>Download swatch file</button>
<?  if (Permission::Sufficient('staff')){ ?>
			<button class='blue edit typcn typcn-pencil'>Edit metadata</button>
<?php   if ($Appearance['id']){ ?>
			<button class='red delete typcn typcn-trash'>Delete apperance</button>
<?php   }
	} ?>
		</div>

<?  if (!empty($Changes))
		echo str_replace('@',CGUtils::GetChangesHTML($Changes),CGUtils::CHANGES_SECTION);
	if ($Appearance['id'] !== 0 && ($CGDb->where('ponyid',$Appearance['id'])->has('tagged') || Permission::Sufficient('staff'))){ ?>
		<section id="tags">
			<h2><span class='typcn typcn-tags'></span>Tags</h2>
			<div class='tags'><?=Appearances::GetTagsHTML($Appearance['id'],NOWRAP)?></div>
		</section>
<?php
	}
	echo Appearances::GetRelatedEpisodesHTML($Appearance, $EQG);
	if (!empty($Appearance['notes'])){ ?>
		<section>
			<h2><span class='typcn typcn-info-large'></span>Additional notes</h2>
			<p id="notes"><?=Appearances::GetNotesHTML($Appearance, NOWRAP, NOTE_TEXT_ONLY)?></p>
		</section>
<?  }

	if (!empty($Appearance['cm_favme'])){
		$preview = Appearances::GetCMPreviewURL($Appearance); ?>
		<section class="approved-cutie-mark">
			<h2>Recommended cutie mark vector</h2>
<?=Permission::Sufficient('staff')&&!isset($Appearance['cm_dir'])?CoreUtils::notice('fail','Missing CM orientation, falling back to <strong>Tail-Head</strong>. Please edit the appaearance and provide an orientation!'):''?>
			<a id="pony-cm" href="http://fav.me/<?=$Appearance['cm_favme']?>" style="background-image:url('<?=Appearances::GetCMPreviewSVGURL($Appearance['id'])?>')">
				<div class="img cm-dir-<?=$Appearance['cm_dir']===CM_DIR_HEAD_TO_TAIL?'ht':'th'?>" style="background-image:url('<?=CoreUtils::aposEncode($preview)?>')"></div>
			</a>
			<p class="aside">This is only an illustration, the body shape & colors are <strong>not</strong> guaranteed to reflect the actual design.</p>
			<p>The image above links to the vector made by <?php
				$Vector = DeviantArt::GetCachedSubmission($Appearance['cm_favme']);
				echo Users::Get($Vector['author'],'name','name, avatar_url')->getProfileLink(User::LINKFORMAT_FULL);
			?> and shows which way the cutie mark should be facing.</p>
		</section>
<?  } ?>
		<section class="color-list">
			<h2 class="admin">Color groups</h2>
			<div class="admin">
				<button class="darkblue typcn typcn-arrow-unsorted reorder-cgs">Re-order groups</button>
				<button class="green typcn typcn-plus create-cg">Create group</button>
			</div>
<?  if ($placehold = Appearances::GetPendingPlaceholderFor($Appearance))
		echo $placehold;
	else { ?>
			<ul id="colors" class="colors"><?php
		$CGs = ColorGroups::Get($Appearance['id']);
		$AllColors = ColorGroups::GetColorsForEach($CGs);
		foreach ($CGs as $cg)
			echo ColorGroups::GetHTML($cg, $AllColors, WRAP, NO_COLON, OUTPUT_COLOR_NAMES);
			?></ul>
		</section>
		<?=Appearances::GetRelatedHTML(Appearances::GetRelated($Appearance['id']))?>
	</div>
<?  } ?>
</div>

<?  $export = array(
		'Color' => $Color,
		'color' => $color,
		'EQG' => $EQG,
		'AppearancePage' => true,
	);
	if (Permission::Sufficient('staff'))
		$export = array_merge($export, array(
			'TAG_TYPES_ASSOC' => Tags::$TAG_TYPES_ASSOC,
			'MAX_SIZE' => CoreUtils::getMaxUploadSize(),
			'HEX_COLOR_PATTERN' => $HEX_COLOR_REGEX,
		));
	echo CoreUtils::exportVars($export); ?>

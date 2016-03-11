<div id="content">
	<div class="sprite-wrap"><?=get_sprite_html($Appearance)?></div>
	<h1><?=$heading?></h1>
	<p>from the MLP-VectorClub <a href="/<?=$color?>guide"><?=$Color?> Guide</a></p>

<?php
	$RenderPath = APPATH."img/cg_render/{$Appearance['id']}.png";
	$FileModTime = '?t='.(file_exists($RenderPath) ? filemtime($RenderPath) : time()); ?>
	<div id="p<?=$Appearance['id']?>">
		<div class='align-center'>
			<a class='darkblue btn typcn typcn-image' href='/<?=$color?>guide/appearance/<?="{$Appearance['id']}.png$FileModTime"?>' target='_blank'>View as PNG</a>
<?  if (PERM('inspector')){ ?>
			<button class='blue edit typcn typcn-pencil'>Edit metadata</button>
			<button class='red delete typcn typcn-trash'>Delete apperance</button>
<?  } ?>
		</div>

<?  if (!empty($Changes)){ ?>
		<section>
			<label><span class='typcn typcn-warning'></span>List of major changes</label>
			<?=render_changes_html($Changes)?>
		</section>
<?  }
	if ($Appearance['id'] !== 0 && ($CGDb->where('ponyid',$Appearance['id'])->has('tagged') || PERM('inspector'))){ ?>
		<section id="tags">
			<label><span class='typcn typcn-tags'></span>Tags</label>
			<div class='tags'><?=get_tags_html($Appearance['id'],NOWRAP)?></div>
		</section>
<?php
	}
	echo get_episode_appearances($Appearance['id']);
	if (!empty($Appearance['notes'])){ ?>
		<section>
			<label><span class='typcn typcn-info-large'></span>Additional notes</label>
			<p id="notes"><?=get_notes_html($Appearance, NOWRAP, NOTE_TEXT_ONLY)?></p>
		</section>
<?  }

	if (!empty($Appearance['cm_favme'])){
		$preview = get_cm_preview_url($Appearance); ?>
		<section class="approved-cutie-mark">
			<label>Recommended cutie mark vector</label>
<?=PERM('inspector')&&!isset($Appearance['cm_dir'])?Notice('fail','Missing CM orientation, falling back to <strong>Tail-Head</strong>. Please edit the appaearance and provide an orientation!'):''?>
			<a id="pony-cm" href="http://fav.me/<?=$Appearance['cm_favme']?>" style="background-image:url('/colorguide/appearance/<?=$Appearance['id']?>.svg')">
				<div class="img cm-dir-<?=$Appearance['cm_dir']===CM_DIR_HEAD_TO_TAIL?'ht':'th'?>" style="background-image:url('<?=apos_encode($preview)?>')"></div>
			</a>
			<p class="aside">This is only an illustration, the body shape & colors are <strong>not</strong> guaranteed to reflect the actual design.</p>
			<p>The image above links to the vector made by <?php
				$Vector = da_cache_deviation($Appearance['cm_favme']);
				echo profile_link(get_user($Vector['author'],'name','name, avatar_url'), FULL);
			?> and shows which way the cutie mark should be facing.</p>
		</section>
<?  } ?>
		<section class="color-list">
			<label class="admin">Color groups</label>
			<div class="admin">
				<button class="darkblue typcn typcn-arrow-unsorted reorder-cgs">Re-order groups</button>
				<button class="green typcn typcn-plus create-cg">Create group</button>
			</div>
			<ul id="colors" class="colors"><?php
	foreach (get_cgs($Appearance['id']) as $cg)
		echo get_cg_html($cg, WRAP, NO_COLON, OUTPUT_COLOR_NAMES);
			?></ul>
		</section>
	</div>
</div>

<?  $export = array(
		'Color' => $Color,
		'color' => $color,
		'EQG' => $EQG,
		'AppearancePage' => true,
	);
	if (PERM('inspector'))
		$export = array_merge($export, array(
			'TAG_TYPES_ASSOC' => $TAG_TYPES_ASSOC,
			'MAX_SIZE' => get_max_upload_size(),
			'HEX_COLOR_PATTERN' => $HEX_COLOR_PATTERN,
		));
	ExportVars($export); ?>

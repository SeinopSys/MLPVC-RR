<div id="content">
	<?=(file_exists(APPATH.($relpath="img/cg/{$Appearance['id']}.png")))?"<img src='/$relpath' alt='{$Appearance['id']}'>":''?>
	<h1><?=$heading?></h1>
	<p>from the MLP-VectorClub <a href="/<?=$color?>guide"><?=$Color?> Guide</a></p>
<?php
	$RenderPath = APPATH."img/cg_render/{$Appearance['id']}.png";
	$FileModTime = '?t='.(file_exists($RenderPath) ? filemtime($RenderPath) : time());
	echo "<div class='align-center'><a class='darkblue btn typcn typcn-image' href='/{$color}guide/appearance/{$Appearance['id']}.png$FileModTime' target='_blank'>View as PNG</a></div>";

	if (!empty($Changes)){ ?>
	<section>
		<label><span class='typcn typcn-warning'></span>List of major changes</label>
		<?=render_changes_html($Changes)?>
	</section>
<?  }
	if ($CGDb->where('ponyid',$Appearance['id'])->has('tagged')){ ?>
	<section id="tags">
		<label><span class='typcn typcn-tags'></span>Tags</label>
		<?=get_tags_html($Appearance['id'],WRAP,NO_INPUT)?>
	</section>
<?php
	}
	$EpTagsOnAppearance = $CGDb->rawQuery(
		"SELECT t.tid
		FROM tagged tt
		LEFT JOIN tags t ON tt.tid = t.tid
		WHERE tt.ponyid = {$Appearance['id']}");
	if (!empty($EpTagsOnAppearance)){
		foreach ($EpTagsOnAppearance as $k => $row)
			$EpTagsOnAppearance[$k] = $row['tid'];

	$EpAppearances = $CGDb->rawQuery(
		"SELECT DISTINCT t.name
		FROM tagged tt
		LEFT JOIN tags t ON tt.tid = t.tid
		WHERE t.type = 'ep' && t.tid IN (".implode(',',$EpTagsOnAppearance).")");
	if (!empty($EpAppearances)){ ?>
	<section id="ep-appearances">
		<label><span class='typcn typcn-video'></span>Appears in <?=plur('episode',count($EpAppearances),PREPEND_NUMBER)?></label>
		<p><?php
			$HTML = '';
			foreach ($EpAppearances as $ep){
				list($season, $episode) = explode('e',substr($ep['name'],1));
				$Ep = get_real_episode($season, $episode);
				$HTML .= empty($Ep)
					? strtoupper($ep['name']).', '
					: "<a href='/episode/S{$Ep['season']}E{$Ep['episode']}'>{$Ep['title']}</a>, ";
			}
			echo rtrim($HTML, ', ')
		?></p>
	</section>
<?php
		}
	}
	if (!empty($Appearance['notes'])){ ?>
	<section>
		<label><span class='typcn typcn-info-large'></span>Additional notes</label>
		<p id="notes"><?=$Appearance['notes']?></p>
	</section>
<?  }
	if (!empty($Appearance['cm_favme'])){
		$CM = da_cache_deviation($Appearance['cm_favme']); ?>
	<section>
		<label>Approved cutie mark vector</label>
		<a id="pony-cm" href="http://fav.me/<?=$CM['id']?>">
			<img src="<?=$CM['preview']?>" alt="<?=$CM['title']?>">
		</a>
	</section>
<?  } ?>
	<ul id="colors"><?
	foreach (get_cgs($Appearance['id']) as $cg)
		echo get_cg_html($cg, WRAP, NO_COLON); ?></ul>
</div>

<script>var Color = '<?=$Color?>', color = '<?=$color?>';</script>

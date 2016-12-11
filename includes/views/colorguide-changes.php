<?php
use App\CoreUtils;
use App\CGUtils;
use App\Tags;
/** @var $heading string */
/** @var $Changes array */ ?>
<div id="content">
	<h1><?=$heading?></h1>
	<p>Displaying <?=$Pagination->itemsPerPage?> items/page</p>
	<p class='align-center links'>
		<a class='btn darkblue typcn typcn-arrow-back' href="/cg">Back to <?=$Color?> Guide</a>
		<a class='btn darkblue typcn typcn-tags' href="/cg/tags">List of tags</a>
	</p>
	<?=$Pagination->HTML . CGUtils::getChangesHTML($Changes, WRAP, SHOW_APPEARANCE_NAMES) . $Pagination->HTML?>
</div>

<?  echo CoreUtils::exportVars(array(
		'Color' => $Color,
		'color' => $color,
		'TAG_TYPES_ASSOC' => Tags::$TAG_TYPES_ASSOC,
	));

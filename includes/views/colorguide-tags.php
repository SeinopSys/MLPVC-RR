<?php
use App\Tags;
use App\CoreUtils;
use App\Permission; ?>
<div id="content">
	<h1><?=$heading?></h1>
	<p>Displaying <?=$Pagination->itemsPerPage?> items/page</p>
	<p class='align-center links'>
		<a class='btn darkblue typcn typcn-arrow-back' href="/cg">Back to <?=$Color?> Guide</a>
		<a class='btn darkblue typcn typcn-warning' href="/cg/changes">Major Changes</a>
	</p>
	<?=$Pagination->HTML?>
	<table id="tags">
		<thead><?php
	$cspan = Permission::Sufficient('staff') ? '" colspan="2' : '';
	$refresher = Permission::Sufficient('staff') ? " <button class='typcn typcn-arrow-sync refresh-all' title='Refresh usage data on this page'></button>" : '';
	echo $thead = <<<HTML
			<tr>
				<th class="tid">ID</th>
				<th class="name{$cspan}">Name</th>
				<th class="title">Description</th>
				<th class="type">Type</th>
				<th class="uses">Uses$refresher</th>
			</tr>
HTML;
?></thead>
		<?=Tags::GetTagListHTML($Tags)?>
		<tfoot><?=$thead?></tfoot>
	</table>
	<?=$Pagination->HTML?>
</div>

<?  CoreUtils::ExportVars(array(
		'Color' => $Color,
		'color' => $color,
		'TAG_TYPES_ASSOC' => Tags::$TAG_TYPES_ASSOC,
	));

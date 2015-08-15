<div id=content>
	<h1><?=$heading?></h1>
	<p>A searchable<sup title="To Be Implemented">TBI</sup> list of <del style=opacity:.3;color:red>every</del> <ins style=color:green>some</ins> characters <?=$color?> keyed so far</p>
	<div class="notice warn tagediting">
		<label>Some features are unavailable</label>
		<p>Because you seem to be using a mobile device, editing tags & colors may not work, as it requires you to right-click. If you want to do either of these, please do so from a computer.</p>
	</div>
	<div id=universal>
		<div>
			<strong>Universal colors</strong>
			<div class="notes">These colors apply to most characters in the show. Unless a different color is specified, use these.</div>
			<ul class="colors static">
				<li>
					<span class=cat>Normal:</span>
					<span style=background-color:#FFFFFF title='Teeth Fill'>#FFFFFF</span>
					<span style=background-color:#B0D8E7 title='Teeth Outline'>#B0D8E7</span>
					<span style=background-color:#AD047A title='Mouth Fill'>#AD047A</span>
					<span style=background-color:#860059 title='Mouth Dark Fill'>#860059</span>
					<span style=background-color:#FF6600 title='Tongue'>#FF6600</span>
					<span style=background-color:#CB4607 title='Tongue Dark'>#CB4607</span>
					<span style=background-color:#000000 title='Emotional Turmoil (up to 15% opacity)'>#000000</span>
				</li>
				<li>
					<span class=cat>Discorded (Partial):</span>
					<span style=background-color:#CECECE title='Teeth Outline'>#CECECE</span>
					<span style=background-color:#92376D title='Mouth Fill'>#92376D</span>
					<span style=background-color:#702050 title='Mouth Dark Fill'>#702050</span>
					<span style=background-color:#BEA1BB title='Tongue'>#BEA1BB</span>
					<span style=background-color:#966D92 title='Tongue Dark'>#966D92</span>
				</li>
				<li>
					<span class=cat>Discorded (Total):</span>
					<span style=background-color:#CCCDD3 title='Teeth Outline'>#CCCDD3</span>
					<span style=background-color:#6D6765 title='Mouth Fill'>#6D6765</span>
					<span style=background-color:#444140 title='Mouth Dark Fill'>#444140</span>
					<span style=background-color:#ABAAA8 title='Tongue'>#ABAAA8</span>
					<span style=background-color:#828180 title='Tongue Dark'>#828180</span>
				</li>
			</ul>
		</div>
	</div>
	<p class=align-center><button class='green typcn typcn-plus' id="new-appearance-btn">Add new appearance</button></p>
	<?=$Pagination = get_pagination_html($color.'guide',$Page,$MaxPages)?>
	<?=get_ponies_html($Ponies)?>
	<?=$Pagination?>
</div>
<div id=sidebar>
<?php include "views/sidebar.php"; ?>
</div>

<script>var Color = '<?=$Color?>', color = '<?=$color?>';</script>
<?php if (PERM('inspector')){ ?>
<script>var TAG_TYPES_ASSOC = <?=json_encode($TAG_TYPES_ASSOC)?>, MAX_SIZE = '<?=get_max_upload_size()?>', PRINTABLE_ASCII_REGEX = '<?=PRINTABLE_ASCII_REGEX?>', HEX_COLOR_PATTERN = <?=rtrim(HEX_COLOR_PATTERN,'u')?>;</script>
<?php } ?>

<?php

	$HexPattern = regex_replace(new RegExp('^/(.*)/.*$'),'$1',$HEX_COLOR_REGEX->jsExport());
	CoreUtils::LoadPage(array(
		'title' => "$Color Blending Calculator",
		'do-css', 'do-js',
	));

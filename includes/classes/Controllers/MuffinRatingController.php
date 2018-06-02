<?php

namespace App\Controllers;
use App\File;
use App\RegExp;

class MuffinRatingController extends Controller {
	protected static $auth = false;

	public function image(){
		$ScorePercent = 100;
		if (isset($_GET['w']) && is_numeric($_GET['w']))
			$ScorePercent = min(max(\intval($_GET['w'], 10), 0), 100);
		$RatingFile = File::get(APPATH.'img/muffin-rating.svg');
		header('Content-Type: image/svg+xml');
		echo str_replace("width='100'", "width='$ScorePercent'", $RatingFile);
		exit;
	}
}

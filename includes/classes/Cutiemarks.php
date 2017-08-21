<?php

namespace App;

use App\Exceptions\MismatchedProviderException;
use App\Models\Appearance;
use App\Models\Cutiemark;
use App\Models\User;

class Cutiemarks {
	/**
	 * @param Appearance $Appearance
	 * @param bool       $procSym
	 *
	 * @return Cutiemark[]|null
	 */
	public static function get(Appearance $Appearance, bool $procSym = true){
		/** @var $CMs Cutiemark[] */
		$CMs = DB::$instance->where('appearance_id', $Appearance->id)->get(Cutiemark::$table_name);
		if ($procSym)
			self::processSymmetrical($CMs);
		return $CMs;
	}

	/** @param Cutiemark[] $CMs */
	public static function processSymmetrical(&$CMs){
		if (count($CMs) === 1 && $CMs[0]->facing === null){
			$CMs[1] = new Cutiemark($CMs[0]->to_array());
			$CMs[0]->facing = 'left';
			$CMs[1]->facing = 'right';
			$CMs[1]->rotation = $CMs[0]->rotation*-1;
		}
	}

	const VALID_FACING_VALUES = ['left','right'];

	/**
	 * @param Cutiemark[] $CutieMarks
	 * @param bool        $wrap
	 *
	 * @return string
	 */
	public static function getListForAppearancePage($CutieMarks, $wrap = WRAP){
		$HTML = '';
		foreach ($CutieMarks as $cm){
			$HTML .= self::getListItemForAppearancePage($cm);
		}

		return $wrap ? "<ul id='pony-cm-list'>$HTML</ul>" : $HTML;
	}

	/**
	 * @param Cutiemark $cm
	 * @param bool      $wrap
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getListItemForAppearancePage(Cutiemark $cm, $wrap = WRAP){
		$facing = $cm->facing !== null ? 'Facing '.CoreUtils::capitalize($cm->facing) : 'Symmetrical';
		$facingSVG = Appearances::getCMFacingSVGURL($cm);
		$preview = CoreUtils::aposEncode($cm->getVectorURL());

		$links = "<a href='/cg/cutiemark/download/{$cm->id}' class='btn link typcn typcn-download'>SVG</a>";
		if (Permission::sufficient('staff'))
			$links .= "<a href='/cg/cutiemark/download/{$cm->id}?source' class='btn darkblue typcn typcn-download'>Original</a>";
		if (($cm->favme ?? null) !== null)
			$links .= "<a href='http://fav.me/{$cm->favme}' class='btn btn-da typcn'>Source</a>";

		$madeby = '';
		if ($cm->contributor !== null){
			$userlink = $cm->contributor->toAnchor(User::WITH_AVATAR);
			$madeby = "<span class='madeby'>Contributed by $userlink</span>";
		}
		$content = <<<HTML
<span class="title">$facing</span>
<div class="preview" style="background-image:url('{$facingSVG}')">
	<div class="img" style="transform: rotate({$cm->rotation}deg); background-image:url('{$preview}')"></div>
</div>
<div class="dl-links">$links</div>
$madeby
HTML;
		return $wrap ? "<li class='pony-cm'>$content</li>" : $content;
	}

	// null (=symmetric) is stringified to '' by implode
	const VALID_FACING_COMBOS = ['left,right','left','right',''];

	/**
	 * @param Cutiemark $data
	 * @param int       $index
	 *
	 * @return bool
	 */
	public static function postProcess(Cutiemark $data, int $index):bool {
		$favme = isset($_POST['favme'][$index]) ? CoreUtils::trim($_POST['favme'][$index]) : null;
		if (empty($favme)){
			if ($index > 0)
				return false;
			Response::fail('Deviation link is missing');
		}

		if (isset($_POST['facing'][$index])){
			$facing = CoreUtils::trim($_POST['facing'][$index]);
			if (empty($facing))
				$facing = null;
			else if (!in_array($facing,Cutiemarks::VALID_FACING_VALUES,true))
				Response::fail('Body orientation is invalid');
		}
		else $facing = null;
		$data->facing = $facing;

		try {
			$Image = new ImageProvider($favme, ['fav.me', 'dA']);
			$favme = $Image->id;
		}
		catch (MismatchedProviderException $e){
			Response::fail('The cutie mark vector must be on DeviantArt, '.$e->getActualProvider().' links are not allowed');
		}
		catch (\Exception $e){ Response::fail('Cutie Mark link issue: '.$e->getMessage()); }
		if (!CoreUtils::isDeviationInClub($favme))
			Response::fail('The cutie mark vector must be in the group gallery');
		$data->favme = $favme;

		if (!isset($_POST['favme_rotation'][$index]))
			Response::fail('Preview rotation amount is missing');
		$favme_rotation = (int) $_POST['favme_rotation'][$index];
		if (!is_numeric($favme_rotation))
			Response::fail('Preview rotation must be a number');
		if ($favme_rotation < -180 || $favme_rotation > 180)
			Response::fail('Preview rotation must be between -180 and 180');
		$data->rotation = $favme_rotation;

		return true;
	}

	/**
	 * @param Cutiemark[] $CMs
	 * @return string
	 */
	public static function convertDataForLogs($CMs):string {
		foreach ($CMs as $k => $v)
			$CMs[$k] = $v->to_array([
				'except' => 'appearance_id',
			]);
		return JSON::encode($CMs);
	}
}

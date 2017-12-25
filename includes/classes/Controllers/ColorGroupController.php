<?php

namespace App\Controllers;
use App\Auth;
use App\CGUtils;
use App\CoreUtils;
use App\Cutiemarks;
use App\DB;
use App\Input;
use App\JSON;
use App\Logs;
use App\Models\Appearance;
use App\Models\Color;
use App\Models\ColorGroup;
use App\Models\Logs\MajorChange;
use App\Permission;
use App\Response;
use App\Users;
use GuzzleHttp\Exception\BadResponseException;

class ColorGroupController extends ColorGuideController {
	public function action($params){
		global $HEX_COLOR_REGEX;

		$this->_initPersonal($params, false);
		if (!Auth::$signed_in)
			Response::fail();
		$isStaff = Permission::sufficient('staff');

		$action = $params['action'];
		$adding = $action === 'make';

		if (!$adding){
			if (empty($params['id']))
				Response::fail('Missing color group ID');
			$GroupID = \intval($params['id'], 10);
			$Group = ColorGroup::find($GroupID);
			if (empty($Group))
				Response::fail("There’s no color group with the ID of $GroupID");
			if (!$isStaff && ($Group->appearance->owner_id === null || $Group->appearance->owner_id !== Auth::$user->id))
				Response::fail();

			if ($action === 'get'){
				$out = $Group->to_array();
				$out['Colors'] = [];
				foreach ($Group->colors as $c){
					$append = $c->to_array([
						'except' => 'group_id',
					]);
					if ($c->linked_to !== null)
						$append['appearance'] = DB::$instance->querySingle(
							'SELECT p.id, p.label FROM appearances p
							LEFT JOIN color_groups cg ON cg.appearance_id = p.id
							LEFT JOIN colors c ON c.group_id = cg.id
							WHERE c.id = ?', [$c->linked_to]);
					$out['Colors'][] = $append;
				}
				Response::done($out);
			}

			if ($action === 'del'){
				$Appearance = $Group->appearance;

				$Group->delete();

				Logs::logAction('cgs', [
					'action' => 'del',
					'group_id' => $Group->id,
					'appearance_id' => $Group->appearance_id,
					'label' => $Group->label,
					'order' => $Group->order,
				]);

				$Appearance->checkSpriteColors();

				Response::success('Color group deleted successfully');
			}
		}
		else $Group = new ColorGroup();

		if ($adding){
			$ponyid = (new Input('ponyid','int', [
				Input::CUSTOM_ERROR_MESSAGES => [
					Input::ERROR_MISSING => 'Missing appearance ID',
				]
			]))->out();
			$params['id'] = $ponyid;
			$this->_getAppearance($params);
			if (!$isStaff && !$this->_isOwnedByUser)
				Response::fail();
			$Group->appearance_id = $ponyid;
		}

		if (!$adding)
			$oldlabel = $Group->label;
		$label = (new Input('label','string', [
			Input::IN_RANGE => [2,30],
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'Color group label is missing',
				Input::ERROR_RANGE => 'Color group label must be between @min and @max characters long',
			]
		]))->out();
		CoreUtils::checkStringValidity($label, 'Color group label', INVERSE_PRINTABLE_ASCII_PATTERN, true);
		if (!$adding)
			DB::$instance->where('id',$Group->id,'!=');
		if (DB::$instance->where('appearance_id', $Group->appearance_id)->where('label', $label)->has(ColorGroup::$table_name))
			Response::fail('There is already a color group with the same name on this appearance.');
		$Group->label = $label;

		if ($Group->appearance->owner_id === null){
			$major = isset($_POST['major']);
			if ($major){
				$reason = (new Input('reason','string', [
					Input::IN_RANGE => [null,255],
					Input::CUSTOM_ERROR_MESSAGES => [
						Input::ERROR_MISSING => 'Please specify a reason for the changes',
						Input::ERROR_RANGE => 'The reason cannot be longer than @max characters',
					],
				]))->out();
				CoreUtils::checkStringValidity($reason, 'Change reason', INVERSE_PRINTABLE_ASCII_PATTERN);
			}
		}

		$Group->save();

		$oldcolors = $adding ? null : $Group->colors;
		$oldColorIDs = [];
		if (!$adding){
			foreach ($oldcolors as $oc)
				$oldColorIDs[] = $oc->id;
		}

		/** @var $recvColors array */
		$recvColors = (new Input('Colors','json', [
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'Missing list of colors',
				Input::ERROR_INVALID => 'List of colors is invalid',
			]
		]))->out();
		/** @var $newcolors Color[] */
		$newcolors = [];
		/** @var $recvColorIDs int[] */
		$recvColorIDs = [];
		/** @var $check_colors_of Appearance[] */
		$check_colors_of = [];
		foreach ($recvColors as $part => $c){
			if (!empty($c['id'])){
				$append = Color::find($c['id']);
				if (empty($append))
					Response::fail("Trying to edit color with ID {$c['id']} which does not exist");
				if ($append->group_id !== $Group->id)
					Response::fail("Trying to modify color with ID {$c['id']} which is not part of the color group you're editing");
				$append->order = $part+1;
				$index = "(ID: {$c['id']})";
				$recvColorIDs[] = $c['id'];
			}
			else {
				$append = new Color([
					'group_id' => $Group->id,
					'order' => $part+1,
				]);
				$index = "(index: $part)";
			}

			if (empty($c['label']))
				Response::fail("You must specify a color name $index");
			$label = CoreUtils::trim($c['label']);
			CoreUtils::checkStringValidity($label, "Color $index name", INVERSE_PRINTABLE_ASCII_PATTERN);
			$ll = mb_strlen($label);
			if ($ll < 3 || $ll > 30)
				Response::fail("The color name must be between 3 and 30 characters in length $index");
			$append->label = $label;

			if (empty($c['hex'])){
				if (!empty($c['linked_to'])){
					$link_target = Color::find($c['linked_to']);
					if (empty($link_target))
						Response::fail("Link target color does not exist $index");
					// Regular guide
					if ($link_target->appearance->owner_id === null){
						// linking to PCG
						if ($append->appearance->owner_id !== null)
							Response::fail("Colors of appearances in the official guide cannot link to colors in personal color guides $index");
						// not Staff
						if (Permission::insufficient('staff'))
							Response::fail("Only staff members can edit colors in the official guide $index");
					}
					// Personal color guide
					else {
						// linking to regular guide
						if ($append->appearance->owner_id === null)
							Response::fail("Colors of appearances in personal color guides cannot link to colors in the official guide $index");
						// not (owner of both appearances) and not Staff
						if ($append->appearance->owner_id !== Auth::$user->id && $link_target->appearance->owner_id !== Auth::$user->id && Permission::insufficient('staff'))
							Response::fail();
					}
					if ($link_target->linked_to !== null)
						Response::fail("The target color is already linked to a different color $index");
					if (!empty((array)$append->dependant_colors))
						Response::fail("Some colors point to this color which means it cannot be changed to a link $index");
					$append->linked_to = $link_target->id;
					$append->hex = $link_target->hex;
					if (!isset($check_colors_of[$link_target->appearance_id]))
						$check_colors_of[$link_target->appearance_id] = $link_target->appearance;
				}
			}
			else {
				$hex = CoreUtils::trim($c['hex']);
				if (!$HEX_COLOR_REGEX->match($hex, $_match))
					Response::fail('Hex color '.CoreUtils::escapeHTML($hex)." is invalid, please leave empty or fix $index");
				$append->hex = '#'.strtoupper($_match[1]);
				if ($Group->appearance->owner_id === null)
					$append->hex = CGUtils::roundHex($append->hex);
				$append->linked_to = null;
			}

			$newcolors[] = $append;
		}
		if (!$adding){
			/** @var $removedColorIDs int[] */
			$removedColorIDs = CoreUtils::array_subtract($oldColorIDs, $recvColorIDs);
			$removedColors = [];
			if (!empty($removedColorIDs)){
				/** @var $Affected Color[] */
				$Affected = DB::$instance->where('id', $removedColorIDs)->get('colors');
				foreach ($Affected as $color){
					if (\count((array) $color->dependant_colors) > 0){
						$links = [];
						foreach ($color->dependant_colors as $dep){
							$arranged[$dep->appearance->id][$dep->group_id][$dep->id] = $dep;
							$links[] = implode(' &rsaquo; ',[
								$dep->appearance->toAnchor(),
								$dep->color_group->label,
								$dep->label
							]);
						}
						Response::fail("<p>The colors listed below depend on color #{$color->id} (".CoreUtils::escapeHTML($color->label).'). Please unlink them before deleting this color.</p><ul><li>'.implode('</li><li>', $links).'</li></ul>');
					}

					$removedColors[] = $color;
				}
			}
		}
		$newlabels = [];
		foreach ($newcolors as $color){
			if (isset($newlabels[$color->label]))
				Response::fail('The color name "'.CoreUtils::escapeHTML($color->label).'" appears in this color group more than once. Please choose a unique name or add numbering to the colors.');

			$newlabels[$color->label] = true;
		}
		unset($newlabels);
		#### Validation ends here - No removal/modification of any colors before this point ####

		$colorError = false;
		foreach ($newcolors as $c){
			if ($c->save())
				continue;

			$colorError = true;
			CoreUtils::error_log(__METHOD__.': Database error triggered by user '.Auth::$user->name.' ('.Auth::$user->id.") while saving colors:\n".JSON::encode($c->errors, JSON_PRETTY_PRINT));
		}
		if (!$adding && !empty($removedColors)){
			foreach ($removedColors as $color)
				$color->delete();
		}
		/** @var $newcolors Color[] */
		if ($colorError)
			Response::fail("There were some issues while saving the colors. Please <a class='send-feedback'>let us know</a> about this error, so we can look into why it might've happened.");

		if (!isset($check_colors_of[$Group->appearance_id]))
			$check_colors_of[$Group->appearance_id] = $Group->appearance;
		$isCMGroup = $Group->label === 'Cutie Mark';
		foreach ($check_colors_of as $appearance){
			$appearance->checkSpriteColors();
			$appearance->clearRenderedImages([Appearance::CLEAR_CMDIR, Appearance::CLEAR_PALETTE, Appearance::CLEAR_PREVIEW]);
			if ($isCMGroup)
				$appearance->clearRenderedImages([Appearance::CLEAR_CM]);
		}

		$colon = !$this->_appearancePage;
		$outputNames = $this->_appearancePage;

		$response = ['cgs' => $Group->appearance->getColorsHTML(NOWRAP, $colon, $outputNames)];

		if ($Group->appearance->owner_id === null && $major){
			Logs::logAction('major_changes', [
				'appearance_id' => $Group->appearance_id,
				'reason' => $reason,
			]);
			if ($this->_appearancePage){
				$FullChangesSection = isset($_POST['FULL_CHANGES_SECTION']);
				$response['changes'] = CGUtils::getChangesHTML(MajorChange::get($Group->appearance_id), $FullChangesSection);
				if ($FullChangesSection)
					$response['changes'] = str_replace('@',$response['changes'],CGUtils::CHANGES_SECTION);
			}
			else $response['update'] = $Group->appearance->getUpdatesHTML();
		}

		if (isset($_POST['APPEARANCE_PAGE']))
			$response['cm_list'] = Cutiemarks::getListForAppearancePage(CutieMarks::get($Group->appearance), NOWRAP);
		else $response['notes'] = Appearance::find($Group->appearance_id)->getNotesHTML(NOWRAP);

		$logdata = [];
		if ($adding) Logs::logAction('cgs', [
			'action' => 'add',
			'group_id' => $Group->id,
			'appearance_id' => $Group->appearance_id,
			'label' => $Group->label,
			'order' => $Group->order,
		]);
		else if ($Group->label !== $oldlabel){
			$logdata['oldlabel'] = $oldlabel;
			$logdata['newlabel'] = $Group->label;
		}

		$oldcolorstr = CGUtils::stringifyColors($oldcolors);
		$newcolorstr = CGUtils::stringifyColors($newcolors);
		$colorsChanged = $oldcolorstr !== $newcolorstr;
		if ($colorsChanged){
			$logdata['oldcolors'] = $oldcolorstr;
			$logdata['newcolors'] = $newcolorstr;
		}
		if (!empty($logdata)){
			$logdata['group_id'] = $Group->id;
			$logdata['appearance_id'] = $Group->appearance_id;
			Logs::logAction('cg_modify', $logdata);
		}

		Response::done($response);
	}

	public function appearanceList(){
		$list = [];
		$personalGuide = $_POST['PERSONAL_GUIDE'] ?? null;
		if ($personalGuide !== null){
			$owner = Users::get($personalGuide, 'name');
			if (empty($owner))
				Response::fail('Personal Color Guide owner could not be found');
			$cond = ['owner_id = ?', $owner->id];
		}
		else $cond = 'owner_id IS NULL';

		foreach (Appearance::all([
			'conditions' => $cond,
			'select' => 'id, label, ishuman',
			'order' => 'label asc',
		]) as $item)
			$list[] = $item->to_array();
		Response::done([ 'list' =>  $list, 'pcg' => $personalGuide !== null ]);
	}

	public function list($params){
		$this->_getAppearance($params);

		$returnedColorFields = [
			isset($_GET['hex']) ? 'hex' : 'id',
			'label',
		];

		$list = [];
		foreach ($this->_appearance->color_groups as $item){
			$group = [
				'label' => $item->label,
				'colors' => []
			];
			foreach ($item->colors as $c){
				$arr = $c->to_array(['only' => $returnedColorFields]);
				if ($c->linked_to !== null)
					unset($arr['id']);
				$group['colors'][] = $arr;
			}
			if (\count($group['colors']) > 0)
				$list[] = $group;
		}
		Response::done([ 'list' =>  $list ]);
	}
}

<?php

	if (POST_REQUEST || (isset($_GET['s']) && $data === "gettags")){
		if (!Permission::Sufficient('staff')) Response::Fail();
		if (POST_REQUEST) CSRFProtection::Protect();

		$EQG = isset($_REQUEST['eqg']) ? 1 : 0;
		$AppearancePage = isset($_POST['APPEARANCE_PAGE']);

		switch ($data){
			case 'gettags':
				$not_tid = (new Input('not','int',array(Input::IS_OPTIONAL => true)))->out();
				if ((new Input('action','string',array(Input::IS_OPTIONAL => true)))->out() === 'synon'){
					if (isset($not_tid))
						$CGDb->where('tid',$not_tid);
					$Tag = $CGDb->where('"synonym_of" IS NOT NULL')->getOne('tags');
					if (!empty($Tag)){
						$Syn = \CG\Tags::GetSynonymOf($Tag,'name');
						Response::Fail("This tag is already a synonym of <strong>{$Syn['name']}</strong>.<br>Would you like to remove the synonym?",array('undo' => true));
					}
				}

				$viaAutocomplete = !empty($_GET['s']);
				$limit = null;
				$cols = "tid, name, type";
				if ($viaAutocomplete){
					if (!regex_match($TAG_NAME_REGEX, $_GET['s']))
						CGUtils::AutocompleteRespond('[]');

					$query = CoreUtils::Trim(strtolower($_GET['s']));
					$TagCheck = CGUtils::CheckEpisodeTagName($query);
					if ($TagCheck !== false)
						$query = $TagCheck;
					$CGDb->where('name',"%$query%",'LIKE');
					$limit = 5;
					$cols = "tid, name, 'typ-'||type as type";
					$CGDb->orderBy('uses','DESC');
				}
				else $CGDb->orderBy('type','ASC')->where('"synonym_of" IS NULL');

				if (isset($not_tid))
					$CGDb->where('tid',$not_tid,'!=');
				$Tags = $CGDb->orderBy('name','ASC')->get('tags',$limit,"$cols, uses, synonym_of");
				if ($viaAutocomplete)
					foreach ($Tags as &$t){
						if (empty($t['synonym_of']))
							continue;
						$Syn = $CGDb->where('tid', $t['synonym_of'])->getOne('tags','name');
						if (!empty($Syn))
							$t['synonym_target'] = $Syn['name'];
					};

				CGUtils::AutocompleteRespond(empty($Tags) ? '[]' : $Tags);
			break;
			case 'full':
				if (!isset($_REQUEST['reorder']))
					CoreUtils::NotFound();

				if (!Permission::Sufficient('staff'))
					Response::Fail();

				\CG\Appearances::Reorder((new Input('list','int[]',array(
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'The list of IDs is missing',
						Input::ERROR_INVALID => 'The list of IDs is not formatted properly',
					)
				)))->out());

				Response::Done(array('html' => CGUtils::GetFullListHTML(\CG\Appearances::Get($EQG,null,'id,label'), true, NOWRAP)));
			break;
			case "export":
				if (!Permission::Sufficient('developer'))
					CoreUtils::NotFound();
				$JSON = array(
					'Appearances' => array(),
					'Tags' => array(),
				);

				$Tags = $CGDb->orderBy('tid','ASC')->get('tags');
				if (!empty($Tags)) foreach ($Tags as $t){
					$JSON['Tags'][$t['tid']] = $t;
				}

				$Appearances = \CG\Appearances::Get(null);
				if (!empty($Appearances)) foreach ($Appearances as $p){
					$AppendAppearance = $p;

					$AppendAppearance['notes'] = CoreUtils::TrimMultiline($AppendAppearance['notes']);

					$AppendAppearance['ColorGroups'] = array();
					$ColorGroups = \CG\ColorGroups::Get($p['id']);
					if (!empty($ColorGroups)){
						$AllColors = \CG\ColorGroups::GetColorsForEach($ColorGroups);
						foreach ($ColorGroups as $cg){
							$AppendColorGroup = $cg;
							unset($AppendColorGroup['ponyid']);

							$AppendColorGroup['Colors'] = array();
							if (!empty($AllColors[$cg['groupid']]))
								foreach ($AllColors[$cg['groupid']] as $c){
									unset($c['groupid']);
									$AppendColorGroup['Colors'][] = $c;
								};

							$AppendAppearance['ColorGroups'][$cg['groupid']] = $AppendColorGroup;
						}
					}

					$AppendAppearance['TagIDs'] = array();
					$TagIDs = \CG\Tags::GetFor($p['id'],null,null,true);
					if (!empty($TagIDs))
						foreach ($TagIDs as $t)
							$AppendAppearance['TagIDs'][] = $t['tid'];

					$JSON['Appearances'][$p['id']] = $AppendAppearance;
				}

				$data = JSON::Encode($JSON, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
				$data = preg_replace_callback('/^\s+/m', function($match){
					return str_pad('',strlen($match[0])/4,"\t", STR_PAD_LEFT);
				}, $data);

				CoreUtils::DownloadFile($data, 'mlpvc-colorguide.json');
			break;
		}

		$_match = array();
		if (regex_match(new RegExp('^(rename|delete|make|(?:[gs]et|del)(?:sprite|cgs)?|tag|untag|clearrendercache|applytemplate)(?:/(\d+))?$'), $data, $_match)){
			$action = $_match[1];
			$creating = $action === 'make';

			if (!$creating){
				$AppearanceID = intval($_match[2], 10);
				if (strlen($_match[2]) === 0)
					Response::Fail('Missing appearance ID');
				$Appearance = $CGDb->where('id', $AppearanceID)->where('ishuman', $EQG)->getOne('appearances');
				if (empty($Appearance))
					Response::Fail("The specified appearance does not exist");
			}
			else $Appearance = array('id' => null);

			switch ($action){
				case "get":
					Response::Done(array(
						'label' => $Appearance['label'],
						'notes' => $Appearance['notes'],
						'cm_favme' => !empty($Appearance['cm_favme']) ? "http://fav.me/{$Appearance['cm_favme']}" : null,
						'cm_preview' => $Appearance['cm_preview'],
						'cm_dir' => isset($Appearance['cm_dir'])
							? ($Appearance['cm_dir'] === CM_DIR_HEAD_TO_TAIL ? 'ht' : 'th')
							: null
					));
				break;
				case "set":
				case "make":
					$data = array(
						'ishuman' => $EQG,
					    'cm_favme' => null,
					);

					$label = (new Input('label','string',array(
						Input::IN_RANGE => [4,70],
						Input::CUSTOM_ERROR_MESSAGES => array(
							Input::ERROR_MISSING => 'Appearance name is missing',
							Input::ERROR_RANGE => 'Appearance name must be beetween @min and @max characters long',
						)
					)))->out();
					CoreUtils::CheckStringValidity($label, "Appearance name", INVERSE_PRINTABLE_ASCII_PATTERN);
					if ($creating && $CGDb->where('label', $label)->has('appearances'))
						Response::Fail('An appearance already esists with this name');
					$data['label'] = $label;

					$notes = (new Input('notes','text',array(
						Input::IS_OPTIONAL => true,
						Input::IN_RANGE => $creating || $Appearance['id'] !== 0 ? [null,1000] : null,
						Input::CUSTOM_ERROR_MESSAGES => array(
							Input::ERROR_RANGE => 'Appearance notes cannot be longer than @max characters',
						)
					)))->out();
					if (isset($notes)){
						CoreUtils::CheckStringValidity($notes, "Appearance notes", INVERSE_PRINTABLE_ASCII_PATTERN);
						$notes = CoreUtils::SanitizeHtml($notes);
						if ($creating || $notes !== $Appearance['notes'])
							$data['notes'] = $notes;
					}
					else $data['notes'] = '';

					$cm_favme = (new Input('cm_favme','string',array(Input::IS_OPTIONAL => true)))->out();
					if (isset($cm_favme)){
						try {
							$Image = new ImageProvider($cm_favme, array('fav.me', 'dA'));
							CoreUtils::CheckDeviationInClub($Image->id, true);
							$data['cm_favme'] = $Image->id;
						}
						catch (MismatchedProviderException $e){
							Response::Fail('The vector must be on DeviantArt, '.$e->getActualProvider().' links are not allowed');
						}
						catch (Exception $e){ Response::Fail("Cutie Mark link issue: ".$e->getMessage()); }

						$cm_dir = (new Input('cm_dir',function($value){
							if ($value !== 'th' && $value !== 'ht')
								return Input::ERROR_INVALID;
						},array(
							Input::CUSTOM_ERROR_MESSAGES => array(
								Input::ERROR_MISSING => 'Cutie mark orientation must be set if a link is provided',
								Input::ERROR_INVALID => 'Cutie mark orientation (@value) is invalid',
							)
						)))->out();
						$cm_dir = $cm_dir === 'ht' ? CM_DIR_HEAD_TO_TAIL : CM_DIR_TAIL_TO_HEAD;
						if ($creating || $Appearance['cm_dir'] !== $cm_dir)
							$data['cm_dir'] = $cm_dir;

						$cm_preview = (new Input('cm_preview','string',array(Input::IS_OPTIONAL => true)))->out();
						if (empty($cm_preview))
							$data['cm_preview'] = null;
						else if ($creating || $cm_preview !== $Appearance['cm_preview']){
							try {
								$Image = new ImageProvider($cm_preview);
								$data['cm_preview'] = $Image->preview;
							}
							catch (Exception $e){ Response::Fail("Cutie Mark preview issue: ".$e->getMessage()); }
						}
					}
					else {
						$data['cm_dir'] = null;
						$data['cm_preview'] = null;
					}

					$query = $creating
						? $CGDb->insert('appearances', $data, 'id')
						: $CGDb->where('id', $Appearance['id'])->update('appearances', $data);
					if (!$query)
						Response::DBError();

					if ($creating){
						$data['id'] = $query;
						$response = array(
							'message' => 'Appearance added successfully',
							'id' => $query,
						);
						if (isset($_POST['template'])){
							try {
								\CG\Appearances::ApplyTemplate($query, $EQG);
							}
							catch (Exception $e){
								$response['message'] .= ", but applying the template failed";
								$response['info'] = "The common color groups could not be added.<br>Reason: ".$e->getMessage();
								Response::Done($response);
							}
						}
						Response::Done($response);
					}
					else {
						CGUtils::ClearRenderedImages($Appearance['id']);
						if ($AppearancePage)
							Response::Done();
					}

					$EditedAppearance = array_merge($Appearance, $data);
					$response = array('label' => $EditedAppearance['label']);
					if ($data['label'] !== $Appearance['label'])
						$response['newurl'] = $Appearance['id'].'-'.\CG\Appearances::GetSafeLabel($EditedAppearance);
					$response['notes'] = \CG\Appearances::GetNotesHTML($EditedAppearance, NOWRAP);
					Response::Done($response);
				break;
				case "delete":
					if ($Appearance['id'] === 0)
						Response::Fail('This appearance cannot be deleted');

					if (!$CGDb->where('id', $Appearance['id'])->delete('appearances'))
						Response::DBError();

					$fpath = APPATH."img/cg/{$Appearance['id']}.png";
					if (file_exists($fpath))
						unlink($fpath);

					CGUtils::ClearRenderedImages($Appearance['id']);

					Response::Success('Appearance removed');
				break;
				case "getcgs":
					$cgs = \CG\ColorGroups::Get($Appearance['id'],'groupid, label');
					if (empty($cgs))
						Response::Fail('This appearance does not have any color groups');
					Response::Done(array('cgs' => $cgs));
				break;
				case "setcgs":
					$groups = (new Input('cgs','int[]',array(
						Input::CUSTOM_ERROR_MESSAGES => array(
							Input::ERROR_MISSING => "$Color group order data missing"
						)
					)))->out();
					foreach ($groups as $part => $GroupID){
						if (!$CGDb->where('groupid', $GroupID)->has('colorgroups'))
							Response::Fail("There's no group with the ID of $GroupID");

						$CGDb->where('groupid', $GroupID)->update('colorgroups',array('order' => $part));
					}

					CGUtils::ClearRenderedImages($Appearance['id']);

					Response::Done(array('cgs' => \CG\Appearances::GetColorsHTML($Appearance['id'], NOWRAP, !$AppearancePage, $AppearancePage)));
				break;
				case "delsprite":
				case "getsprite":
				case "setsprite":
					$fname = $Appearance['id'].'.png';
					$finalpath = SPRITE_PATH.$fname;

					switch ($action){
						case "setsprite":
							CGUtils::ProcessUploadedImage('sprite', $finalpath, array('image/png'), 100);
							CGUtils::ClearRenderedImages($Appearance['id']);
						break;
						case "delsprite":
							if (empty(\CG\Appearances::GetSpriteURL($finalpath)))
								Response::Fail('No sprite file found');

							if (!unlink($finalpath))
								Response::Fail('File could not be deleted');

							Response::Done(array('sprite' => \CG\Appearances::GetSpriteURL($Appearance['id'], DEFAULT_SPRITE)));
						break;
					}

					Response::Done(array("path" => "/cg/v/{$fname}s.png?t=".filemtime($finalpath)));
				break;
				case "clearrendercache":
					if (!CGUtils::ClearRenderedImages($Appearance['id']))
						Response::Fail('Cache could not be cleared');

					Response::Success('Cached images removed, they will be re-generated on the next request');
				break;
				case "tag":
				case "untag":
					if ($Appearance['id'] === 0)
						Response::Fail("This appearance cannot be tagged");

					switch ($action){
						case "tag":
							$tag_name = CGUtils::ValidateTagName('tag_name');

							$TagCheck = CGUtils::CheckEpisodeTagName($tag_name);
							if ($TagCheck !== false)
								$tag_name = $TagCheck;

							$Tag = \CG\Tags::GetActual($tag_name, 'name');
							if (empty($Tag))
								Response::Fail("The tag $tag_name does not exist.<br>Would you like to create it?",array(
									'cancreate' => $tag_name,
									'typehint' => $TagCheck !== false ? 'ep' : null,
								));

							if ($CGDb->where('ponyid', $Appearance['id'])->where('tid', $Tag['tid'])->has('tagged'))
								Response::Fail('This appearance already has this tag');

							if (!$CGDb->insert('tagged',array(
								'ponyid' => $Appearance['id'],
								'tid' => $Tag['tid'],
							))) Response::DBError();
						break;
						case "untag":
							$tag_id = (new Input('tag','int',array(
								Input::CUSTOM_ERROR_MESSAGES => array (
									Input::ERROR_MISSING => 'Tag ID is missing',
									Input::ERROR_INVALID => 'Tag ID (@value) is invalid',
								)
							)))->out();
							$Tag = $CGDb->where('tid',$tag_id)->getOne('tags');
							if (empty($Tag))
								Response::Fail('This tag does not exist');
							if (!empty($Tag['synonym_of'])){
								$Syn = \CG\Tags::GetSynonymOf($Tag,'name');
								Response::Fail('Synonym tags cannot be removed from appearances directly. '.
								        "If you want to remove this tag you must remove <strong>{$Syn['name']}</strong> or the synonymization.");
							}

							if ($CGDb->where('ponyid', $Appearance['id'])->where('tid', $Tag['tid'])->has('tagged')){
								if (!$CGDb->where('ponyid', $Appearance['id'])->where('tid', $Tag['tid'])->delete('tagged'))
									Response::DBError();
							}
						break;
					}

					\CG\Tags::UpdateUses($Tag['tid']);
					if (isset(CGUtils::$GroupTagIDs_Assoc[$Tag['tid']]))
						\CG\Appearances::GetSortReorder($EQG);

					$response = array('tags' => \CG\Appearances::GetTagsHTML($Appearance['id'], NOWRAP));
					if ($AppearancePage && $Tag['type'] === 'ep'){
						$response['needupdate'] = true;
						$response['eps'] = \CG\Appearances::GetRelatedEpisodesHTML($Appearance['id']);
					}
					Response::Done($response);
				break;
				case "applytemplate":
					try {
						\CG\Appearances::ApplyTemplate($Appearance['id'], $EQG);
					}
					catch (Exception $e){
						Response::Fail("Applying the template failed. Reason: ".$e->getMessage());
					}

					Response::Done(array('cgs' => \CG\Appearances::GetColorsHTML($Appearance['id'], NOWRAP, !$AppearancePage, $AppearancePage)));
				break;
				default: CoreUtils::NotFound();
			}
		}
		else if (regex_match(new RegExp('^([gs]et|make|del|merge|recount|(?:un)?synon)tag(?:/(\d+))?$'), $data, $_match)){
			$action = $_match[1];

			if ($action === 'recount'){
				$tagIDs = (new Input('tagids','int[]',array(
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'Missing list of tags to update',
						Input::ERROR_INVALID => 'List of tags is invalid',
					)
				)))->out();
				$counts = array();
				$updates = 0;
				foreach ($tagIDs as $tid){
					if (\CG\Tags::GetActual($tid,'tid',RETURN_AS_BOOL)){
						$result = \CG\Tags::UpdateUses($tid, true);
						if ($result['status'])
							$updates++;
						$counts[$tid] = $result['count'];
					}
				}

				Response::Success(
					(
						!$updates
						? 'There was no change in the tag usage counts'
						: "$updates tag".($updates!==1?"s'":"'s").' use count'.($updates!==1?'s were':' was').' updated'
					),
					array('counts' => $counts)
				);
			}


			// TODO Untangle spaghetti
			$getting = $action === 'get';
			$deleting = $action === 'del';
			$new = $action === 'make';
			$merging = $action === 'merge';
			$synoning = $action === 'synon';
			$unsynoning = $action === 'unsynon';

			if (!$new){
				if (!isset($_match[2]))
					Response::Fail('Missing tag ID');
				$TagID = intval($_match[2], 10);
				$Tag = $CGDb->where('tid', $TagID)->getOne('tags',isset($query) ? 'tid, name, type':'*');
				if (empty($Tag))
					Response::Fail("This tag does not exist");

				if ($getting) Response::Done($Tag);

				if ($deleting){
					$AppearanceID = \CG\Appearances::ValidateAppearancePageID();

					if (!isset($_POST['sanitycheck'])){
						$tid = !empty($Tag['synonym_of']) ? $Tag['synonym_of'] : $Tag['tid'];
						$Uses = $CGDb->where('tid',$tid)->count('tagged');
						if ($Uses > 0)
							Response::Fail('<p>This tag is currently used on '.CoreUtils::MakePlural('appearance',$Uses,PREPEND_NUMBER).'</p><p>Deleting will <strong class="color-red">permanently remove</strong> the tag from those appearances!</p><p>Are you <em class="color-red">REALLY</em> sure about this?</p>',array('confirm' => true));
					}

					if (!$CGDb->where('tid', $Tag['tid'])->delete('tags'))
						Response::DBError();

					if (isset(CGUtils::$GroupTagIDs_Assoc[$Tag['tid']]))
						\CG\Appearances::GetSortReorder($EQG);

					Response::Success('Tag deleted successfully', isset($AppearanceID) && $Tag['type'] === 'ep' ? array(
						'needupdate' => true,
						'eps' => \CG\Appearances::GetRelatedEpisodesHTML($AppearanceID),
					) : null);
				}
			}
			$data = array();

			if ($merging || $synoning){
				if ($synoning && !empty($Tag['synonym_of']))
					Response::Fail('This tag is already synonymized with a different tag');

				$targetid = (new Input('targetid','int',array(
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'Missing target tag ID',
					)
				)))->out();
				$Target = $CGDb->where('tid', $targetid)->getOne('tags');
				if (empty($Target))
					Response::Fail('Target tag does not exist');
				if (!empty($Target['synonym_of']))
					Response::Fail('Synonym tags cannot be synonymization targets');

				$_TargetTagged = $CGDb->where('tid', $Target['tid'])->get('tagged',null,'ponyid');
				$TargetTagged = array();
				foreach ($_TargetTagged as $tg)
					$TargetTagged[] = $tg['ponyid'];

				$Tagged = $CGDb->where('tid', $Tag['tid'])->get('tagged',null,'ponyid');
				foreach ($Tagged as $tg){
					if (in_array($tg['ponyid'], $TargetTagged)) continue;

					if (!$CGDb->insert('tagged',array(
						'tid' => $Target['tid'],
						'ponyid' => $tg['ponyid']
					))) Response::Fail('Tag '.($merging?'merging':'synonimizing')." failed, please re-try.<br>Technical details: ponyid={$tg['ponyid']} tid={$Target['tid']}");
				}
				if ($merging)
					// No need to delete "tagged" table entries, constraints do it for us
					$CGDb->where('tid', $Tag['tid'])->delete('tags');
				else {
					$CGDb->where('tid', $Tag['tid'])->delete('tagged');
					$CGDb->where('tid', $Tag['tid'])->update('tags', array('synonym_of' => $Target['tid'], 'uses' => 0));
				}

				\CG\Tags::UpdateUses($Target['tid']);
				Response::Success('Tags successfully '.($merging?'merged':'synonymized'), $synoning || $merging ? array('target' => $Target) : null);
			}
			else if ($unsynoning){
				if (empty($Tag['synonym_of']))
					Response::Done();

				$keep_tagged = isset($_POST['keep_tagged']);
				$uses = 0;
				if ($keep_tagged){
					$Target = $CGDb->where('tid', $Tag['synonym_of'])->getOne('tags','tid');
					if (!empty($Target)){
						$TargetTagged = $CGDb->where('tid', $Target['tid'])->get('tagged',null,'ponyid');
						foreach ($TargetTagged as $tg){
							if (!$CGDb->insert('tagged',array(
								'tid' => $Tag['tid'],
								'ponyid' => $tg['ponyid']
							))) Response::Fail("Tag synonym removal process failed, please re-try.<br>Technical details: ponyid={$tg['ponyid']} tid={$Tag['tid']}");
							$uses++;
						}
					}
					else $keep_tagged = false;
				}

				if (!$CGDb->where('tid', $Tag['tid'])->update('tags', array('synonym_of' => null, 'uses' => $uses)))
					Response::DBError();

				Response::Done(array('keep_tagged' => $keep_tagged));
			}

			$data['name'] = CGUtils::ValidateTagName('name');

			$epTagName = CGUtils::CheckEpisodeTagName($data['name']);
			$surelyAnEpisodeTag = $epTagName !== false;
			$type = (new Input('type',function($value){
				if (!isset(\CG\Tags::$TAG_TYPES_ASSOC[$value]))
					return Input::ERROR_INVALID;
			},array(
				Input::IS_OPTIONAL => true,
				Input::CUSTOM_ERROR_MESSAGES => array(
					Input::ERROR_INVALID => 'Invalid tag type: @value',
				)
			)))->out();
			if (empty($type)){
				if ($surelyAnEpisodeTag)
					$data['name'] = $epTagName;
				$data['type'] = $epTagName === false ? null : 'ep';
			}
			else {
				if ($type == 'ep'){
					if (!$surelyAnEpisodeTag)
						Response::Fail('Episode tags must be in the format of <strong>s##e##[-##]</strong> where # represents a number<br>Allowed seasons: 1-8, episodes: 1-26');
					$data['name'] = $epTagName;
				}
				else if ($surelyAnEpisodeTag)
					$type = $ep;
				$data['type'] = $type;
			}

			if (!$new) $CGDb->where('tid',$Tag['tid'],'!=');
			if ($CGDb->where('name', $data['name'])->where('type', $data['type'])->has('tags') || $data['name'] === 'wrong cutie mark')
				Response::Fail("A tag with the same name and type already exists");

			$data['title'] = (new Input('title','string',array(
				Input::IS_OPTIONAL => true,
				Input::IN_RANGE => [null,255],
				Input::CUSTOM_ERROR_MESSAGES => array(
					Input::ERROR_RANGE => 'Tag title must fit within @max characters'
				)
			)))->out();

			if ($new){
				$TagID = $CGDb->insert('tags', $data, 'tid');
				if (!$TagID) Response::DBError();
				$data['tid'] = $TagID;

				$AppearanceID = (new Input('addto','int',array(Input::IS_OPTIONAL => true)))->out();
				if (isset($AppearanceID)){
					if ($AppearanceID === 0)
						Response::Success("The tag was created, <strong>but</strong> it could not be added to the appearance because it can't be tagged.");

					$Appearance = $CGDb->where('id', $AppearanceID)->getOne('appearances');
					if (empty($Appearance))
						Response::Success("The tag was created, <strong>but</strong> it could not be added to the appearance (<a href='/cg/v/$AppearanceID'>#$AppearanceID</a>) because it doesn't seem to exist. Please try adding the tag manually.");

					if (!$CGDb->insert('tagged',array(
						'tid' => $data['tid'],
						'ponyid' => $Appearance['id']
					))) Response::DBError();
					\CG\Tags::UpdateUses($data['tid']);
					$r = array('tags' => \CG\Appearances::GetTagsHTML($Appearance['id'], NOWRAP));
					if ($AppearancePage){
						$r['needupdate'] = true;
						$r['eps'] = \CG\Appearances::GetRelatedEpisodesHTML($Appearance['id']);
					}
					Response::Done($r);
				}
			}
			else {
				$CGDb->where('tid', $Tag['tid'])->update('tags', $data);
				$data = array_merge($Tag, $data);
			}

			Response::Done($data);
		}
		else if (regex_match(new RegExp('^([gs]et|make|del)cg(?:/(\d+))?$'), $data, $_match)){
			$action = $_match[1];
			$new = $action === 'make';


			if (!$new){
				if (empty($_match[2]))
					Response::Fail('Missing color group ID');
				$GroupID = intval($_match[2], 10);
				$Group = $CGDb->where('groupid', $GroupID)->getOne('colorgroups');
				if (empty($GroupID))
					Response::Fail("There's no $color group with the ID of $GroupID");

				if ($action === 'get'){
					$Group['Colors'] = \CG\ColorGroups::GetColors($Group['groupid']);
					Response::Done($Group);
				}

				if ($action === 'del'){
					if (!$CGDb->where('groupid', $Group['groupid'])->delete('colorgroups'))
						Response::DBError();
					Response::Success("$Color group deleted successfully");
				}
			}
			$data = array();

			$data['label'] = (new Input('label','string',array(
				Input::IN_RANGE => [2,30],
				Input::CUSTOM_ERROR_MESSAGES => array(
					Input::ERROR_MISSING => 'Please specify a group name',
					Input::ERROR_RANGE => 'The group name must be between @min and @max characters in length',
				)
			)))->out();
			CoreUtils::CheckStringValidity($data['label'], "$Color group name", INVERSE_PRINTABLE_ASCII_PATTERN, true);

			$major = isset($_POST['major']);
			if ($major){
				$reason = (new Input('reason','string',array(
					Input::IN_RANGE => [null,255],
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'Please specify a reason for the changes',
						Input::ERROR_RANGE => 'The reason must fit within @max characters',
					),
				)))->out();
				CoreUtils::CheckStringValidity($reason, "Change reason", INVERSE_PRINTABLE_ASCII_PATTERN);
			}

			if ($new){
				$AppearanceID = (new Input('ponyid','int',array(
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'Missing appearance ID',
					)
				)))->out();
				$Appearance = $CGDb->where('id', $AppearanceID)->where('ishuman', $EQG)->getOne('appearances');
				if (empty($Appearance))
					Response::Fail('The specified appearance odes not exist');
				$data['ponyid'] = $AppearanceID;

				// Attempt to get order number of last color group for the appearance
				$LastGroup = \CG\ColorGroups::Get($AppearanceID, '"order"', 'DESC', 1);
				$data['order'] =  !empty($LastGroup['order']) ? $LastGroup['order']+1 : 1;

				$GroupID = $CGDb->insert('colorgroups', $data, 'groupid');
				if (!$GroupID)
					Response::DBError();
				$Group = array('groupid' => $GroupID);
			}
			else $CGDb->where('groupid', $Group['groupid'])->update('colorgroups', $data);

			$recvColors = (new Input('Colors','json',array(
				Input::CUSTOM_ERROR_MESSAGES => array(
					Input::ERROR_MISSING => "Missing list of {$color}s",
					Input::ERROR_INVALID => "List of {$color}s is invalid",
				)
			)))->out();
			$colors = array();
			foreach ($recvColors as $part => $c){
				$append = array('order' => $part);
				$index = "(index: $part)";

				if (empty($c['label']))
					Response::Fail("You must specify a $color name $index");
				$label = CoreUtils::Trim($c['label']);
				CoreUtils::CheckStringValidity($label, "$Color $index name", INVERSE_PRINTABLE_ASCII_PATTERN);
				$ll = strlen($label);
				if ($ll < 3 || $ll > 30)
					Response::Fail("The $color name must be between 3 and 30 characters in length $index");
				$append['label'] = $label;

				if (empty($c['hex']))
					Response::Fail("You must specify a $color code $index");
				$hex = CoreUtils::Trim($c['hex']);
				if (!$HEX_COLOR_REGEX->match($hex, $_match))
					Response::Fail("HEX $color is in an invalid format $index");
				$append['hex'] = '#'.strtoupper($_match[1]);

				$colors[] = $append;
			}
			if (!$new)
				$CGDb->where('groupid', $Group['groupid'])->delete('colors');
			$colorError = false;
			foreach ($colors as $i => $c){
				$c['groupid'] = $Group['groupid'];
				if (!$CGDb->insert('colors', $c) && !$colorError)
					$colorError = true;
			}
			if ($colorError)
				Response::Fail("There were some issues while saving some of the colors. Please let the developer know about this error, so he can look into why this might've happened.");

			$colon = !$AppearancePage;
			$outputNames = $AppearancePage;

			if ($new) $response = array('cgs' => \CG\Appearances::GetColorsHTML($Appearance['id'], NOWRAP, $colon, $outputNames));
			else $response = array('cg' => \CG\ColorGroups::GetHTML($Group['groupid'], null, NOWRAP, $colon, $outputNames));

			$AppearanceID = $new ? $Appearance['id'] : $Group['ponyid'];
			if ($major){
				Log::Action('color_modify',array(
					'ponyid' => $AppearanceID,
					'reason' => $reason,
				));
				$response['update'] = \CG\Appearances::GetUpdatesHTML($AppearanceID);
			}
			CGUtils::ClearRenderedImages($AppearanceID);

			if (isset($_POST['APPEARANCE_PAGE']))
				$response['cm_img'] = "/cg/v/$AppearanceID.svg?t=".time();
			else $response['notes'] = \CG\Appearances::GetNotesHTML($CGDb->where('id', $AppearanceID)->getOne('appearances'),  NOWRAP);

			Response::Done($response);
		}
		else CoreUtils::NotFound();
	}

	if (regex_match(new RegExp('^tags'),$data)){
		$Pagination = new Pagination("cg/tags", 20, $CGDb->count('tags'));

		CoreUtils::FixPath("/cg/tags/{$Pagination->page}");
		$heading = "Tags";
		$title = "Page $Pagination->page - $heading - $Color Guide";

		$Tags = \CG\Tags::GetFor(null,$Pagination->GetLimit(), true);

		if (isset($_GET['js']))
			$Pagination->Respond(\CG\Tags::GetTagListHTML($Tags, NOWRAP), '#tags tbody');

		$js = array('paginate');
		if (Permission::Sufficient('staff'))
			$js[] = "$do-tags";

		CoreUtils::LoadPage(array(
			'title' => $title,
			'heading' => $heading,
			'view' => "$do-tags",
			'css' => "$do-tags",
			'js' => $js,
		));
	}

	if (regex_match(new RegExp('^changes'),$data)){
		$Pagination = new Pagination("cg/changes", 50, $Database->count('log__color_modify'));

		CoreUtils::FixPath("/cg/changes/{$Pagination->page}");
		$heading = "Major $Color Changes";
		$title = "Page $Pagination->page - $heading - $Color Guide";

		$Changes = \CG\Updates::Get(null, $Pagination->GetLimitString());

		if (isset($_GET['js']))
			$Pagination->Respond(CGUtils::GetChangesHTML($Changes, NOWRAP, SHOW_APPEARANCE_NAMES), '#changes');

		CoreUtils::LoadPage(array(
			'title' => $title,
			'heading' => $heading,
			'view' => "$do-changes",
			'css' => "$do-changes",
			'js' => 'paginate',
		));
	}

	$EQG = $EQG_URL_PATTERN->match($data) ? 1 : 0;
	if ($EQG)
		$data = $EQG_URL_PATTERN->replace('', $data);
	$CGPath = "/cg".($EQG?'/eqg':'');

	$GUIDE_MANAGE_JS = array(
		'jquery.uploadzone',
		'jquery.autocomplete',
		'handlebars-v3.0.3',
		'Sortable',
		"$do-tags",
		"$do-manage",
	);
	$GUIDE_MANAGE_CSS = array(
		"$do-manage",
	);

	$_match = array();
	if (regex_match(new RegExp('^(?:appearance|v)/(?:.*?(\d+)|(\d+)(?:-.*)?)(?:(s)?\.(png|svg|json|gpl))?'),$data,$_match)){
		$asFile = !empty($_match[4]);
		$Appearance = $CGDb->where('id', intval($_match[1]??$_match[2], 10))->getOne('appearances', $asFile ? 'id,label,cm_dir,ishuman' : null);
		if (empty($Appearance))
			CoreUtils::NotFound();

		if ($Appearance['ishuman'] && !$EQG){
			$EQG = 1;
			$CGPath = '/cg/eqg';
		}
		else if (!$Appearance['ishuman'] && $EQG){
			$EQG = 0;
			$CGPath = '/cg';
		}

		if ($asFile){
			switch ($_match[4]){
				case 'png':
					if ($_match[3] === 's')
						CGUtils::RenderSpritePNG($Appearance['id']);
					CGUtils::RenderAppearancePNG($Appearance);
				case 'svg': CGUtils::RenderCMDirectionSVG($Appearance['id'], $Appearance['cm_dir']);
				case 'json': CGUtils::GetSwatchesAI($Appearance); 
				case 'gpl': CGUtils::GetSwatchesInkscape($Appearance); 
			}
			# rendering functions internally call die(), so execution stops here #
		}

		$SafeLabel = \CG\Appearances::GetSafeLabel($Appearance);
		CoreUtils::FixPath("$CGPath/v/{$Appearance['id']}-$SafeLabel");
		$title = $heading = $Appearance['label'];
		if ($Appearance['id'] === 0 && $color !== 'color')
			$title = str_replace('color',$color,$title);

		$Changes = \CG\Updates::Get($Appearance['id']);

		$settings = array(
			'title' => "$title - $Color Guide",
			'heading' => $heading,
			'view' => "$do-single",
			'css' => array($do, "$do-single"),
			'js' => array('jquery.qtip', 'jquery.ctxmenu', $do, "$do-single"),
		);
		if (Permission::Sufficient('staff')){
			$settings['css'] = array_merge($settings['css'], $GUIDE_MANAGE_CSS);
			$settings['js'] = array_merge($settings['js'],$GUIDE_MANAGE_JS);
		}
		CoreUtils::LoadPage($settings);
	}
	else if (regex_match(new RegExp('^sprite-colou?rs/(\d+)(?:-.*)?$'),$data,$_match)){
		$Appearance = $CGDb->where('id', intval($_match[1], 10))->getOne('appearances', 'id,label');
		if (empty($Appearance))
			CoreUtils::NotFound();

		$Map = CGUtils::GetSpriteImageMap($Appearance['id']);
		if (empty($Map)){
			CGUtils::RenderSpritePNG($Appearance['id'], STAY_ALIVE);
			$Map = CGUtils::GetSpriteImageMap($Appearance['id']);
			if (empty($Map))
				CoreUtils::NotFound();
		}
		$ColorMap = \CG\Appearances::GetSpriteColorMap($Appearance['id']);

		$ColorGroups = \CG\ColorGroups::Get($Appearance['id'], 'groupid,label');
		$SortedColorGroups = array();
		foreach ($ColorGroups as $cg)
			$SortedColorGroups[$cg['groupid']] = $cg;

		$GroupedColors = \CG\ColorGroups::GetColorsForEach($ColorGroups);
		$Colors = array();
		foreach ($GroupedColors as $cg){
			foreach ($cg as $c){
				$c['label'] = $SortedColorGroups[$c['groupid']]['label'].' | '.$c['label'];
				$Colors[] = $c;
			}
		}

		$IMGWidth = $Map['width'];
		$IMGHeight = $Map['height'];
		$SVG = "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 $IMGWidth $IMGHeight' enable-background='new 0 0 $IMGWidth $IMGHeight' xml:space='preserve' xmlns:data='http://example.com/data'>";
		foreach ($Map['linedata'] as $line){
			$hex = $ColorMap[$line['hex']];
			if ($line['opacity'] !== 1)
				$hex .= "' opacity='{$line['opacity']}";
			$SVG .= "<rect x='{$line['x']}px' y='{$line['y']}px' width='{$line['width']}px' height='1px' fill='$hex' data:ph='{$line['hex']}'/>";
		}
		$SVG .= '</svg>';

		$SafeLabel = \CG\Appearances::GetSafeLabel($Appearance);
		CoreUtils::FixPath("$CGPath/sprite-{$color}s/{$Appearance['id']}-$SafeLabel");

		CoreUtils::LoadPage(array(
			'view' => "$do-spriteedit",
			'title' => "Sprite of {$Appearance['label']}",
			'css' => "$do-spriteedit",
			'js' => "$do-spriteedit",
		));
	}
	else if ($data === 'full'){
		$GuideOrder = !isset($_REQUEST['alphabetically']) && !$EQG;
		if (!$GuideOrder)
			$CGDb->orderBy('label','ASC');
		$Appearances = \CG\Appearances::Get($EQG,null,'id,label');

		if (isset($_REQUEST['ajax']))
			Response::Done(array('html' => CGUtils::GetFullListHTML($Appearances, $GuideOrder, NOWRAP)));

		$js = array();
		if (Permission::Sufficient('staff'))
			$js[] = 'Sortable';
		$js[] = "$do-full";

		CoreUtils::LoadPage(array(
			'title' => "Full List - $Color Guide",
			'view' => "$do-full",
			'css' => "$do-full",
			'js' => $js,
		));
	}

	$title = '';
	$AppearancesPerPage = UserPrefs::Get('cg_itemsperpage');
	if (empty($_GET['q']) || regex_match(new RegExp('^\*+$'),$_GET['q'])){
		$_EntryCount = $CGDb->where('ishuman',$EQG)->where('id != 0')->count('appearances');

		$Pagination = new Pagination("cg", $AppearancesPerPage, $_EntryCount);
		$Ponies = \CG\Appearances::Get($EQG, $Pagination->GetLimit());
	}
	else {
		$SearchQuery = $_GET['q'];
		$Ponies = null;

		try {
			$Search = CGUtils::ProcessSearch($SearchQuery);
			$title .= "$SearchQuery - ";
			$IsHuman = $EQG ? 'true' : 'false';

			$Restrictions = array();
			$Params = array();
			if (!empty($Search['tid'])){
				$tc = count($Search['tid']);
				$Restrictions[] = 'p.id IN (
					SELECT t.ponyid
					FROM tagged t
					WHERE t.tid IN ('.implode(',', $Search['tid']).")
					GROUP BY t.ponyid
					HAVING COUNT(t.tid) = $tc
				)";
				$Search['tid_assoc'] = array();
				foreach ($Search['tid'] as $tid)
					$Search['tid_assoc'][$tid] = true;
			}
			if (!empty($Search['label'])){
				$collect = array();
				foreach ($Search['label'] as $l){
					$collect[] = 'lower(p.label) LIKE ?';
					$Params[] = $l;
				}
				$Restrictions[] = implode(' AND ', $collect);
			}

			if (count($Restrictions)){
				$Params[] = $EQG;
				$Query = "SELECT @coloumn FROM appearances p WHERE ".implode(' AND ',$Restrictions)." AND p.ishuman = ? AND p.id != 0";
				$_EntryCount = $CGDb->rawQuerySingle(str_replace('@coloumn','COUNT(*) as count',$Query),$Params)['count'];
				$Pagination = new Pagination("cg", $AppearancesPerPage, $_EntryCount);

				$SearchSQLQuery = str_replace('@coloumn','p.*',$Query);
				$SearchSQLQuery .= " ORDER BY p.order ASC {$Pagination->GetLimitString()}";
				$Ponies = $CGDb->rawQuery($SearchSQLQuery,$Params);
			}
		}
		catch (Exception $e){
			$_MSG = $e->getMessage();
			if (isset($_REQUEST['js']))
				Response::Done($_MSG);
		}

		if (empty($Pagination))
			$Pagination = new Pagination("cg", $AppearancesPerPage, 0);
	}
	if (isset($_REQUEST['GOFAST']))
		Response::Done(array('goto' => "/cg/v/{$Ponies[0]['id']}"));

	CoreUtils::FixPath("$CGPath/{$Pagination->page}".(!empty($Restrictions)?"?q=$SearchQuery":''));
	$heading = ($EQG?'EQG ':'')."$Color Guide";
	$title .= "Page {$Pagination->page} - $heading";

	if (isset($_GET['js']))
		$Pagination->Respond(\CG\Appearances::GetHTML($Ponies, NOWRAP), '#list');

	$settings = array(
		'title' => $title,
		'heading' => $heading,
		'css' => array($do),
		'js' => array('jquery.qtip', 'jquery.ctxmenu', $do, 'paginate'),
	);
	if (Permission::Sufficient('staff')){
		$settings['css'] = array_merge($settings['css'], $GUIDE_MANAGE_CSS);
		$settings['js'] = array_merge($settings['js'], $GUIDE_MANAGE_JS);
	}
	CoreUtils::LoadPage($settings);

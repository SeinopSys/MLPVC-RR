<?php

use App\CoreUtils;
use App\Input;
use App\Logs;
use App\Pagination;
use App\Permission;
use App\RegExp;
use App\Response;
use App\Users;

/** @var $do string */
/** @var $data string */

if (!Permission::sufficient('staff'))
	CoreUtils::notFound();

$task = strtok($data, '/');
$data = preg_replace(new RegExp('^[^/]*?(?:/(.*))?$'), '$1', $data);

if (POST_REQUEST){
	switch ($task){
		case "logs":
			if (preg_match(new RegExp('^details/(\d+)'), $data, $_match)){
				$EntryID = intval($_match[1], 10);

				$MainEntry = $Database->where('entryid', $EntryID)->getOne('log');
				if (empty($MainEntry))
					Response::fail('Log entry does not exist');
				if (empty($MainEntry['refid']))
					Response::fail('There are no details to show', array('unlickable' => true));

				$Details = $Database->where('entryid', $MainEntry['refid'])->getOne("log__{$MainEntry['reftype']}");
				if (empty($Details)){
					error_log("Could not find details for entry {$MainEntry['reftype']}#{$MainEntry['refid']}, NULL-ing refid of Main#{$MainEntry['entryid']}");
					$Database->where('entryid', $MainEntry['entryid'])->update('log', array('refid' => null));
					Response::fail('Failed to retrieve details', array('unlickable' => true));
				}

				Response::done(Logs::formatEntryDetails($MainEntry,$Details));
			}
			else CoreUtils::notFound();
		break;
		case "usefullinks":
			if (preg_match(new RegExp('^([gs]et|del|make)(?:/(\d+))?$'), $data, $_match)){
				$action = $_match[1];
				$creating = $action === 'make';

				if (!$creating){
					$Link = $Database->where('id', $_match[2])->getOne('usefullinks');
					if (empty($Link))
						Response::fail('The specified link does not exist');
				}

				switch ($action){
					case 'get':
						Response::done(array(
							'label' => $Link['label'],
							'url' => $Link['url'],
							'title' => $Link['title'],
							'minrole' => $Link['minrole'],
						));
					case 'del':
						if (!$Database->where('id', $Link['id'])->delete('usefullinks'))
							Response::dbError();

						Response::done();
					break;
					case 'make':
					case 'set':
						$data = array();

						$label = (new Input('label','string',array(
							Input::IN_RANGE => [3,35],
							Input::CUSTOM_ERROR_MESSAGES => array(
								Input::ERROR_MISSING => 'Link label is missing',
								Input::ERROR_RANGE => 'Link label must be between @min and @max characters long',
							)
						)))->out();
						if ($creating || $Link['label'] !== $label){
							CoreUtils::checkStringValidity($label, 'Link label', INVERSE_PRINTABLE_ASCII_PATTERN);
							$data['label'] = $label;
						}

						$url = (new Input('url','url',array(
							Input::IN_RANGE => [3,255],
							Input::CUSTOM_ERROR_MESSAGES => array(
								Input::ERROR_MISSING => 'Link URL is missing',
								Input::ERROR_RANGE => 'Link URL must be between @min and @max characters long',
							)
						)))->out();
						if ($creating || $Link['url'] !== $url)
							$data['url'] = $url;

						$title = (new Input('title','string',array(
							Input::IS_OPTIONAL => true,
							Input::IN_RANGE => [3,255],
							Input::CUSTOM_ERROR_MESSAGES => array(
								Input::ERROR_RANGE => 'Link title must be between @min and @max characters long',
							)
						)))->out();
						if (!isset($title))
							$data['title'] = '';
						else if ($creating || $Link['title'] !== $title){
							CoreUtils::checkStringValidity($title, 'Link title', INVERSE_PRINTABLE_ASCII_PATTERN);
							$data['title'] = $title;
						}

						$minrole = (new Input('minrole',function($value){
							if (empty(Permission::ROLES_ASSOC[$value]) || !Permission::sufficient('user', $value))
								Response::fail();
						},array(
							Input::CUSTOM_ERROR_MESSAGES => array(
								Input::ERROR_MISSING => 'Minumum role is missing',
								Input::ERROR_INVALID => 'Minumum role (@value) is invalid',
							)
						)))->out();
						if ($creating || $Link['minrole'] !== $minrole)
							$data['minrole'] = $minrole;

						if (empty($data))
							Response::fail('Nothing was changed');
						$query = $creating
							? $Database->insert('usefullinks', $data)
							: $Database->where('id', $Link['id'])->update('usefullinks', $data);
						if (!$query)
							Response::dbError();

						Response::done();
					break;
					default: CoreUtils::notFound();
				}
			}
			else if ($data === 'reorder'){
				$list = (new Input('list','int[]',array(
					Input::CUSTOM_ERROR_MESSAGES => array(
						Input::ERROR_MISSING => 'Missing ordering information',
					)
				)))->out();
				$order = 1;
				foreach ($list as $id){
					if (!$Database->where('id', $id)->update('usefullinks', array('order' => $order++)))
						Response::fail("Updating link #$id failed, process halted");
				}

				Response::done();
			}
			else CoreUtils::notFound();
		break;
		default:
			CoreUtils::notFound();
	}
}

if (empty($task))
	CoreUtils::loadPage(array(
		'title' => 'Admin Area',
		'do-css',
		'js' => array('Sortable',$do),
	));

switch ($task){
	case "logs":
		$type = Logs::validateRefType('type', true, true);
		if (isset($_GET['type']) && preg_match(new RegExp('/^[a-z_]+$/'), $_GET['type']) && isset(Logs::$LOG_DESCRIPTION[$_GET['type']]))
			$type = $_GET['type'];

		if (!isset($_GET['by']))
			$by = null;
		else switch(strtolower(CoreUtils::trim($_GET['by']))){
			case 'me':
			case 'you':
				$initiator = $currentUser->id;
				$by = 'you';
			break;
			case 'web server':
				$initiator = 0;
				$by = 'Web server';
			break;
			default:
				$by = Users::validateName('by', null, true);
				if (isset($by)){
					$by = Users::get($by, 'name', 'id,name');
					$initiator = $by->id;
					$by = $initiator === $currentUser->id ? 'me' : $by->name;
				}
		};

		$title = '';
		function process_filter(&$q = null){
			global $Database, $type, $by, $initiator, $title;

			if (isset($type)){
				$Database->where('reftype', $type);
				if (isset($q)){
					$q[] = "type=$type";
					$title .= Logs::$LOG_DESCRIPTION[$type].' entries ';
				}
			}
			else if (isset($q))
				$q[] = 'type='.CoreUtils::FIXPATH_EMPTY;
			if (isset($initiator)){
				$_params = $initiator === 0 ? array('"initiator" IS NULL') : array('initiator',$initiator);
				$Database->where(...$_params);
				if (isset($q) && isset($by)){
					$q[] = "by=$by";
					$title .= (!isset($type)?'Entries ':'')."by $by ";
				}
			}
			else if (isset($q))
				$q[] = 'by='.CoreUtils::FIXPATH_EMPTY;
		}
		$q = array();
		if (isset($_GET['js']))
			$q[] = 'js='.$_GET['js'];
		process_filter($q);

		$Pagination = new Pagination('admin/logs', 20, $Database->count('log'));

		$heading = 'Global logs';
		if (!empty($title))
			$title .= '- ';
		$title .= "Page {$Pagination->page} - $heading";
		CoreUtils::fixPath("/admin/logs/{$Pagination->page}".(!empty($q)?'?'.implode('&',$q):''));

		process_filter();
		$LogItems = $Database
			->orderBy('timestamp')
			->orderBy('entryid')
			->get('log', $Pagination->getLimit());

		if (isset($_GET['js']))
			$Pagination->respond(Logs::getTbody($LogItems), '#logs tbody');

		CoreUtils::loadPage(array(
			'title' => $title,
			'view' => "$do-logs",
			'css' => "$do-logs",
			'js' => array("$do-logs", 'paginate'),
		));
	break;
	default:
		CoreUtils::notFound();
}

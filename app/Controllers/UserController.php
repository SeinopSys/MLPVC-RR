<?php

namespace App\Controllers;
use App\Auth;
use App\CoreUtils;
use App\CSRFProtection;
use App\DB;
use App\DeviantArt;
use App\File;
use App\GlobalSettings;
use App\HTTP;
use App\Input;
use App\Logs;
use App\Models\Post;
use App\Models\Session;
use App\Models\User;
use App\Pagination;
use App\Permission;
use App\Posts;
use App\Response;
use App\UserPrefs;
use App\Users;

class UserController extends Controller {
	use UserLoaderTrait;

	public function homepage(){
		if (UserPrefs::get('p_homelastep'))
			HTTP::tempRedirect('/episode/latest');

		HTTP::tempRedirect('/cg');
	}

	public function profile($params){
		global $USERNAME_REGEX;

		$un = $params['name'] ?? null;

		$error = null;
		$sub_error = null;
		if ($un === null){
			if (Auth::$signed_in)
				$user = Auth::$user;
			else $error = 'Sign in to view your settings';
		}
		else $user = Users::get($un, 'name');

		if (empty($user) || !($user instanceof User)){
			if (Auth::$signed_in && isset($user) && $user === false){
				if (strpos(Auth::$session->scope, 'browse') !== false){
					$error = 'user does not exist';
					$sub_error = 'Check the name for typos and try again';
				}
				else {
					$error = 'Could not fetch user information';
					$sub_error = 'Your session is missing the "browse" scope';
				}
			}
			else if ($error === null){
				$error = 'Local user data missing';
				if (!Auth::$signed_in){
					$exists = 'exists on DeviantArt';
					if ($un !== null)
						$exists = "<a href='https://www.deviantart.com/".CoreUtils::aposEncode(strtolower($un))."'>$exists</a>";
					$sub_error = "If this user $exists, sign in to import their details.";
				}
			}
			$can_edit = $same_user = $dev_on_dev = false;
		}
		else {
			$pagePath = "/@{$user->name}";
			CoreUtils::fixPath($pagePath);
			$same_user = Auth::$signed_in && $user->id === Auth::$user->id;
			$can_edit = !$same_user && Permission::sufficient('staff') && Permission::sufficient($user->role);
			$dev_on_dev = Permission::sufficient('developer') && Permission::sufficient('developer', $user->role);
		}

		if ($error !== null)
			HTTP::statusCode(404);
		else $sessions = $user->sessions;

		$is_staff = Permission::sufficient('staff');

		if ($same_user || $is_staff){
			if (\count($user->name_changes) > 0){
				$old_names = [];
				foreach ($user->name_changes as $entry)
					$old_names[] = $entry->old;

				$old_names = implode(', ', $old_names);
			}
		}

		$contribs = $user->getCachedContributions();
		$contrib_cache_duration = Users::getContributionsCacheDuration();

		if ($can_edit){
			$export_roles = [];
			$roles_copy = Permission::ROLES_ASSOC;
			unset($roles_copy['guest']);
			foreach ($roles_copy as $name => $label){
				if (Permission::insufficient($name, Auth::$user->role))
					continue;
				$export_roles[$name] = $label;
			}
		}
		else if ($dev_on_dev)
			$export_roles = Permission::ROLES_ASSOC;

		$pcg_section_is_private = UserPrefs::get('p_hidepcg', $user);
		$list_pcgs = !$pcg_section_is_private || $same_user || $is_staff;
		if ($list_pcgs)
			$personal_color_guides = $user->pcg_appearances;

		$settings = [
			'title' => $error === null ? ($same_user?'Your':CoreUtils::posess($user->name)).' '.($same_user || $can_edit?'account':'profile') : 'Account',
			'noindex' => true,
			'css' => [true],
			'js' => ['jquery.fluidbox',true],
			'og' => [
				'image' => $user ? $user->avatar_url : null,
				'description' => $user ? CoreUtils::posess($user->name)." profile on the MLP-VectorClub's website" : null,
			],
			'import' => [
				'user' => $user ?? null,
				'discord_membership' => $user->discord_member,
				'can_edit' => $can_edit,
				'same_user' => $same_user,
				'is_staff' => $is_staff,
				'dev_on_dev' => $dev_on_dev,
				'sessions' => $sessions ?? null,
				'da_logo' => str_replace(' fill="#FFF"','', File::get(APPATH.'img/da-logo.svg')),
				'old_names' => $old_names ?? null,
				'contribs' => $contribs,
				'contrib_cache_duration' => $contrib_cache_duration,
				'export_roles' => $export_roles ?? null,
				'section_is_private' => $pcg_section_is_private,
				'list_pcgs' => $list_pcgs,
				'personal_color_guides' => $personal_color_guides ?? null,
			],
		];
		if ($error !== null)
			$settings['import']['error'] = $error;
		if ($sub_error !== null)
			$settings['import']['sub_error'] = $sub_error;
		if ($can_edit || $dev_on_dev)
			$settings['js'][] = 'pages/user/manage';
		$showSuggestions = $same_user;
		if ($showSuggestions){
			$settings['js'][] = 'pages/user/suggestion';
			$settings['css'][] = 'pages/user/suggestion';
		}
		$settings['import']['showSuggestions'] = $showSuggestions;
		CoreUtils::loadPage(__METHOD__, $settings);
	}

	public function profileByUuid($params){
		if (!isset($params['uuid']) || Permission::insufficient('developer'))
			CoreUtils::notFound();

		/** @var $User User */
		$User = DB::$instance->where('id', $params['uuid'])->getOne('users','name');
		if (empty($User))
			CoreUtils::notFound();

		HTTP::permRedirect('/@'.$User->name);
	}

	public function sessionApi($params){
		if ($this->action !== 'DELETE')
			CoreUtils::notAllowed();

		if (!isset($params['id']))
			Response::fail('Missing session ID');

		$Session = Session::find($params['id']);
		if (empty($Session))
			Response::fail('This session does not exist');
		if ($Session->user_id !== Auth::$user->id && Permission::insufficient('staff'))
			Response::fail('You are not allowed to delete this session');

		$Session->delete();

		Response::success('Session successfully removed');
	}

	public function roleApi($params){
		if ($this->action !== 'PUT')
			CoreUtils::notAllowed();

		if (Permission::insufficient('staff'))
			Response::fail();

		if (!isset($params['id']))
			Response::fail('Missing user ID');

		$targetUser = User::find($params['id']);
		if (empty($targetUser))
			Response::fail('User not found');

		if ($targetUser->id === Auth::$user->id)
			Response::fail('You cannot modify your own group');
		if (Permission::insufficient($targetUser->role))
			Response::fail('You can only modify the group of users who are in the same or a lower-level group than you');

		$newrole = (new Input('newrole','role', [
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'The new group is not specified',
				Input::ERROR_INVALID => 'The specified group (@value) does not exist',
			]
		]))->out();
		if ($targetUser->role === $newrole)
			Response::done(['already_in' => true]);

		$targetUser->updateRole($newrole);

		Response::done();
	}

	public const CONTRIB_NAMES = [
		'cms-provided' => 'Cutie Mark vectors provided',
		'requests' => 'Requests posted',
		'reservations' => 'Reservations posted',
		'finished-posts' => 'Posts finished',
		'fulfilled-requests' => 'Requests fulfilled',
	];

	public function contrib($params){
		if (!isset(self::CONTRIB_NAMES[$params['type']]))
			CoreUtils::notFound();

		$User = Users::get($params['name'], 'name');
		if (empty($User))
			CoreUtils::notFound();
		if ($User->id !== (Auth::$user->id ?? null) && $params['type'] === 'requests' && Permission::insufficient('staff'))
			CoreUtils::notFound();

		$itemsPerPage = 10;
		$Pagination = new Pagination("/@{$User->name}/contrib/{$params['type']}", $itemsPerPage);

		/** @var $cnt int */
		/** @var $data array */
		switch ($params['type']){
			case 'cms-provided':
				$cnt = $User->getCMContributions();
				$Pagination->calcMaxPages($cnt);
				$data = $User->getCMContributions(false, $Pagination);
			break;
			case 'requests':
				$cnt = $User->getRequestContributions();
				$Pagination->calcMaxPages($cnt);
				$data = $User->getRequestContributions(false, $Pagination);
			break;
			case 'reservations':
				$cnt = $User->getReservationContributions();
				$Pagination->calcMaxPages($cnt);
				$data = $User->getReservationContributions(false, $Pagination);
			break;
			case 'finished-posts':
				$cnt = $User->getFinishedPostContributions();
				$Pagination->calcMaxPages($cnt);
				$data = $User->getFinishedPostContributions(false, $Pagination);
			break;
			case 'fulfilled-requests':
				$cnt = $User->getApprovedFinishedRequestContributions();
				$Pagination->calcMaxPages($cnt);
				$data = $User->getApprovedFinishedRequestContributions(false, $Pagination);
			break;
			default:
				throw new \RuntimeException(__METHOD__.": Missing data retriever for type {$params['type']}");
		}

		CoreUtils::fixPath($Pagination->toURI());

		$title = "Page {$Pagination->getPage()} - ".self::CONTRIB_NAMES[$params['type']].' - '.CoreUtils::posess($User->name).' Contributions';
		$heading = self::CONTRIB_NAMES[$params['type']].' by '.$User->toAnchor();
		CoreUtils::loadPage(__METHOD__, [
			'title' => $title,
			'heading' => $heading,
			'css' => [true],
			'js' => ['paginate',true],
			'import' => [
				'data' => $data,
				'params' => $params,
				'Pagination' => $Pagination,
				'itemsPerPage' => $itemsPerPage,
				'User' => $User,
				'contribName' => self::CONTRIB_NAMES[$params['type']],
			],
		]);
	}

	public function contribLazyload($params){
		$CachedDeviation = DeviantArt::getCachedDeviation($params['favme']);
		if (empty($CachedDeviation))
			HTTP::statusCode(404, AND_DIE);

		if (empty($_GET['format']))
			Response::done(['html' => $CachedDeviation->toLinkWithPreview()]);
		else switch ($_GET['format']){
			case 'raw':
				Response::done($CachedDeviation->to_array());
			break;
		}
	}

	public function list(){
		if (Permission::insufficient('staff'))
			CoreUtils::noPerm();

		$Users = DB::$instance->orderBy('name')->get(User::$table_name);

		CoreUtils::loadPage(__METHOD__, [
			'title' => 'Users',
			'css' => [true],
			'import' => [
				'Users' => $Users,
			],
		]);
	}

	public function avatarWrap($params){
		if ($this->action !== 'GET')
			CoreUtils::notAllowed();

		$this->load_user($params);

		Response::done(['html' => $this->user->getAvatarWrap()]);
	}
}

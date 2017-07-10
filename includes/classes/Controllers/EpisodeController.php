<?php

namespace App\Controllers;
use App\Auth;
use App\CGUtils;
use App\CoreUtils;
use App\CSRFProtection;
use App\Episodes;
use App\Input;
use App\JSON;
use App\Logs;
use App\Permission;
use App\Posts;
use App\Response;
use App\VideoProvider;
use \App\Models\Episode;
use \App\Models\EpisodeVideo;

/** @property Episode $_episode */
class EpisodeController extends Controller {
	public function index(){
		$CurrentEpisode = Episodes::getLatest();
		if (empty($CurrentEpisode))
			CoreUtils::loadPage([
				'title' => 'Home',
				'view' => 'episode',
			]);

		Episodes::loadPage($CurrentEpisode);
	}

	public function nextup(){
		// Only accessible from localhost
		if (!in_array($_SERVER['REMOTE_ADDR'],['::1','127.0.0.1']) && Permission::insufficient('developer'))
			Response::fail();

		global $Database;

		/** @var $UpcomingEpisode Episode */
		$UpcomingEpisode = $Database->where('airs > NOW()')->orderBy('airs', 'ASC')->where('season != 0')->getOne('episodes','season,episode,title,airs,twoparter');

		if (empty($UpcomingEpisode))
			Response::fail('No upcoming episode found');

		$out = $UpcomingEpisode->toArray(true);
		unset($out['isMovie']);
		Response::done($out, JSON::PRETTY_PRINT);
	}

	public function page($params){
		Episodes::loadPage($params['id'] ?? null);
	}

	private $_episode;
	private function _getEpisode($params, $required = true){
		$EpData = Episode::parseID(!empty($params['id']) ? $params['id'] : null);
		if (!empty($EpData)){
			$this->_episode = Episodes::getActual($EpData['season'], $EpData['episode'], Episodes::ALLOW_MOVIES);
			if (empty($this->_episode))
				Response::fail('There’s no episode with this season & episode number');
		}
		else if ($required)
			CoreUtils::notFound();
	}

	public function postList($params){
		$this->_getEpisode($params);

		$section = $_GET['section'];
		$only = $section === 'requests' ? ONLY_REQUESTS : ONLY_RESERVATIONS;
		$posts = Posts::get($this->_episode, $only, Permission::sufficient('staff'));

		switch ($only){
			case ONLY_REQUESTS: $rendered = Posts::getRequestsSection($posts); break;
			case ONLY_RESERVATIONS: $rendered = Posts::getReservationsSection($posts); break;
		}
		Response::done(['render' => $rendered]);
	}

	public function get($params){
		CSRFProtection::protect();
		$this->_getEpisode($params);

		Response::done([
			'ep' => $this->_episode,
			'epid' => "S{$this->_episode->season}E{$this->_episode->episode}",
			'caneditid' => $this->_episode->getPostCount() === 0,
		]);
	}

	public function _addEdit($params, $action){
		CSRFProtection::protect();

		if (!Permission::sufficient('staff'))
			Response::fail();

		global $Database, $Database;

		$editing = $action === 'set';
		if ($editing)
			$this->_getEpisode($params);
		$canEditID = !empty($this->_episode) && $this->_episode->getPostCount() === 0;

		$insert = [];
		if (!$editing)
			$insert['posted_by'] = Auth::$user->id;

		if (!$editing || $canEditID){
			$insert['season'] = Episodes::validateSeason(Episodes::ALLOW_MOVIES);
			$isMovie = $insert['season'] === 0;
			$insert['episode'] = Episodes::validateEpisode($isMovie);
		}
		else if (!$canEditID){
			$isMovie = $this->_episode->season === 0;
			$insert['season'] = $isMovie ? 0 : $this->_episode->season;
			$insert['episode'] = $this->_episode->episode;
		}
		$What = $isMovie ? 'Movie' : 'Episode';
		$what = strtolower($What);

		$EpisodeChanged = true;
		$SeasonChanged = true;
		if ($editing){
			$SeasonChanged = $isMovie ? false : (int)$insert['season'] !== $this->_episode->season;
			$EpisodeChanged = (int)$insert['episode'] !== $this->_episode->episode;
			if ($SeasonChanged || $EpisodeChanged){
				$Target = Episodes::getActual(
					$insert['season'] ?? $this->_episode->season,
					$insert['episode'] ?? $this->_episode->episode,
					Episodes::ALLOW_MOVIES
				);
				if (!empty($Target))
					Response::fail('There’s already an episode with the same season & episode number');

				if ((new Episode($insert))->getPostCount() > 0)
					Response::fail('This epsiode’s ID cannot be changed because it already has posts and this action could break existing links');
			}
		}
		else if ($canEditID){
			$MatchingID = $Database->whereEp($insert['season'], $insert['episode'])->getOne('episodes');
			if (!empty($MatchingID))
				Response::fail(($isMovie?'A movie':'An episode').' with the same '.($isMovie?'overall':'season and episode').' number already exists');
		}

		if (!$isMovie)
			$insert['no'] = (new Input('no','int', [
				Input::IS_OPTIONAL => true,
				Input::IN_RANGE => [1,255],
				Input::CUSTOM_ERROR_MESSAGES => [
				    Input::ERROR_INVALID => 'Overall episode number (@value) is invalid',
				    Input::ERROR_RANGE => 'Overall episode number must be between @min and @max',
				]
			]))->out();

		$insert['twoparter'] = !$isMovie  && isset($_POST['twoparter']) ? 1 : 0;
		if ($insert['twoparter']){
			$tempEp = new Episode([
				'season' => $insert['season'],
				'episode' => $insert['episode']+1,
			]);
			if ($Database->whereEp($tempEp)->has('episodes')){
				$tepID = $tempEp->getID();
				Response::fail("This episode cannot have two parts because <a href='/episode/$tepID'>$tepID</a> already exists.");
			}
		}

		$insert['title'] = (new Input('title',function(&$value, $range) use ($isMovie){
			global $PREFIX_REGEX;
			$prefixed = $PREFIX_REGEX->match($value, $match);
			if ($prefixed){
				if (!$isMovie){
					return 'prefix-movieonly';
				}
				if (!isset(Episodes::$ALLOWED_PREFIXES[$match[1]])){
					$mostSimilar = null;
					$mostMatcing = 0;
					foreach (Episodes::$ALLOWED_PREFIXES as $prefix => $shorthand){
						foreach ([$prefix, $shorthand] as $test){
							$matchingChars = similar_text(strtolower($match[1]), strtolower($test));
							if ($matchingChars >= 3 && $matchingChars > $mostMatcing){
								$mostMatcing = $matchingChars;
								$mostSimilar = $prefix;
							}
						}
					}
					Response::fail("Unsupported prefix: {$match[1]}. ".(isset($mostSimilar) ? "<em>Did you mean <span class='color-ui'>$mostSimilar</span></em>?" : ''));
				}

				$title = Episodes::removeTitlePrefix($value);
				if (Input::checkStringLength($title, $range, $code))
					return $code;

				$value = "{$match[1]}: $title";
			}
			else if (Input::checkStringLength($value, $range, $code))
				return $code;
		}, [
			Input::IN_RANGE => [5,35],
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => "$What title is missing",
				Input::ERROR_RANGE => "$What title must be between @min and @max characters",
				'prefix-movieonly' => 'Prefixes can only be used for movies',
			]
		]))->out();
		CoreUtils::checkStringValidity($insert['title'], "$What title", INVERSE_EP_TITLE_PATTERN);

		$airs = (new Input('airs','timestamp', [
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'No air date & time specified',
				Input::ERROR_INVALID => 'Invalid air date and/or time (@value) specified'
			]
		]))->out();
		if (empty($airs))
			Response::fail('Please specify an air date & time');
		$insert['airs'] = date('c',strtotime('this minute', $airs));

		$notes = (new Input('notes','text', [
			Input::IS_OPTIONAL => true,
			Input::IN_RANGE => [null,1000],
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_RANGE => "$What notes cannot be longer than @max characters",
			]
		]))->out();
		if (isset($notes)){
			CoreUtils::checkStringValidity($notes, "$What notes", INVERSE_PRINTABLE_ASCII_PATTERN);
			$notes = CoreUtils::sanitizeHtml($notes);
			if (!$editing || $notes !== $this->_episode->notes)
				$insert['notes'] = $notes;
		}
		else $insert['notes'] = null;

		if ($editing){
			if (!$Database->whereEp($this->_episode)->update('episodes', $insert))
				Response::dbError('Updating episode failed');
		}
		else if (!$Database->insert('episodes', $insert))
			Response::dbError('Episode creation failed');

		if (!$editing || $SeasonChanged || $EpisodeChanged){
			if ($isMovie){
				if ($EpisodeChanged){
					$TagName = CGUtils::checkEpisodeTagName("movie#{$insert['episode']}");
					$MovieTag = $Database->where('name', $editing ? "movie#{$this->_episode->episode}" : $TagName)->getOne('tags', 'tid');

					if (!empty($MovieTag)){
						if ($editing)
							$Database->where('tid', $MovieTag['tid'])->update('tags', [
								'name' => $TagName,
							]);
					}
					else {
						if (!$Database->insert('tags', [
							'name' => $TagName,
							'type' => 'ep',
						])) Response::dbError('Episode tag creation failed');
					}
				}
			}
			else if ($SeasonChanged || $EpisodeChanged){
				$TagName = CGUtils::checkEpisodeTagName("s{$insert['season']}e{$insert['episode']}");
				$EpTag = $Database->where('name', $editing ? "s{$this->_episode->season}e{$this->_episode->episode}" : $TagName)->getOne('tags', 'tid');

				if (!empty($EpTag)){
					if ($editing)
						$Database->where('tid', $EpTag['tid'])->update('tags', [
							'name' => $TagName,
						]);
				}
				else {
					if (!$Database->insert('tags', [
						'name' => $TagName,
						'type' => 'ep',
					])) Response::dbError('Episode tag creation failed');
				}
			}
		}

		if ($editing){
			$logentry = ['target' => $this->_episode->formatTitle(AS_ARRAY,'id')];
			$changes = 0;
			if (!empty($this->_episode->airs))
				$this->_episode->airs = date('c',strtotime($this->_episode->airs));
			foreach (['season', 'episode', 'twoparter', 'title', 'airs'] as $k){
				/** @noinspection TypeUnsafeComparisonInspection */
				if (isset($insert[$k]) && $insert[$k] != $this->_episode->{$k}){
					$logentry["old$k"] = $this->_episode->{$k};
					$logentry["new$k"] = $insert[$k];
					$changes++;
				}
			}
			if ($changes > 0)
				Logs::logAction('episode_modify',$logentry);
		}
		else Logs::logAction('episodes', [
			'action' => 'add',
			'season' => $insert['season'],
			'episode' => $insert['episode'],
			'twoparter' => isset($insert['twoparter']) ? $insert['twoparter'] : 0,
			'title' => $insert['title'],
			'airs' => $insert['airs'],
		]);
		if ($editing)
			Response::done();
		Response::done(['url' => (new Episode($insert))->toURL()]);
	}

	public function set($params){
		$this->_addEdit($params, 'set');
	}

	public function add($params){
		$this->_addEdit($params, 'add');
	}

	public function delete($params){
		$this->_getEpisode($params);

		global $Database, $Database;

		if (!Permission::sufficient('staff'))
			Response::fail();

		if (!$Database->whereEp($this->_episode)->delete('episodes'))
			Response::dbError();
		Logs::logAction('episodes', [
			'action' => 'del',
			'season' => $this->_episode->season,
			'episode' => $this->_episode->episode,
			'twoparter' => $this->_episode->twoparter,
			'title' => $this->_episode->title,
			'airs' => $this->_episode->airs,
		]);
		$Database->where('name', "s{$this->_episode->season}e{$this->_episode->episode}")->where('uses',0)->delete('tags');
		Response::success('Episode deleted successfuly', [
			'upcoming' => Episodes::getSidebarUpcoming(NOWRAP),
		]);
	}

	public function vote($params){
		CSRFProtection::protect();
		$this->_getEpisode($params);

		global $Database;

		if (isset($_REQUEST['detail'])){
			$VoteCountQuery = $Database->rawQuery(
				'SELECT count(*) as value, vote as label
				FROM episodes__votes v
				WHERE season = ? && episode = ?
				GROUP BY v.vote
				ORDER BY v.vote ASC', [$this->_episode->season, $this->_episode->episode]);
			$VoteCounts = [
				'labels' => [],
				'datasets' => [
					[
						'data' => []
					]
				]
			];
			foreach ($VoteCountQuery as $row){
				$VoteCounts['labels'][] = $row['label'];
				$VoteCounts['datasets'][0]['data'][] = $row['value'];
			}

			Response::done(['data' => $VoteCounts]);
		}
		else if (isset($_REQUEST['html']))
			Response::done(['html' => Episodes::getSidebarVoting($this->_episode)]);

		if (!Permission::sufficient('user'))
			Response::fail();

		if (!$this->_episode->aired)
			Response::fail('You can only vote on this episode after it has aired.');

		$UserVote = Episodes::getUserVote($this->_episode);
		if (!empty($UserVote))
			Response::fail('You already voted for this episode');

		$vote = (new Input('vote','int', [
			Input::IN_RANGE => [1,5],
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'Vote value missing from request',
				Input::ERROR_RANGE => 'Vote value must be an integer between @min and @max (inclusive)',
			]
		]))->out();

		if (!$Database->insert('episodes__votes', [
			'season' => $this->_episode->season,
			'episode' => $this->_episode->episode,
			'user' => Auth::$user->id,
			'vote' => $vote,
		])) Response::dbError();
		$this->_episode->updateScore();
		Response::done(['newhtml' => Episodes::getSidebarVoting($this->_episode)]);
	}

	public function getVideoEmbeds($params){
		$this->_getEpisode($params);

		Response::done(Episodes::getVideoEmbeds($this->_episode));
	}

	private function _getVideoData($params){
		$this->_getEpisode($params);

		global $Database;

		$return = [
			'twoparter' => $this->_episode->twoparter,
			'vidlinks' => [],
			'fullep' => [],
			'airs' => date('c',strtotime($this->_episode->airs)),
		];
		/** @var $Vids EpisodeVideo[] */
		$Vids = $Database->whereEp($this->_episode)->get('episodes__videos');
		foreach ($Vids as $part => $vid){
			if (!empty($vid->id))
				$return['vidlinks']["{$vid->provider}_{$vid->part}"] = VideoProvider::getEmbed($vid, VideoProvider::URL_ONLY);
			if ($vid->fullep)
				$return['fullep'][] = $vid->provider;
		}
		Response::done($return);
	}

	private function _setVideoData($params){
		CSRFProtection::protect();
		$this->_getEpisode($params);

		global $Database;

		foreach (['yt', 'dm'] as $provider){
			for ($part = 1; $part <= ($this->_episode->twoparter?2:1); $part++){
				$set = null;
				$PostKey = "{$provider}_$part";
				if (!empty($_POST[$PostKey])){
					$Provider = Episodes::$VIDEO_PROVIDER_NAMES[$provider];
					try {
						$vidProvider = new VideoProvider($_POST[$PostKey]);
					}
					catch (\Exception $e){
						Response::fail("$Provider link issue: ".$e->getMessage());
					};
					if (!isset($vidProvider->episodeVideo) || $vidProvider->episodeVideo->provider !== $provider)
						Response::fail("Incorrect $Provider URL specified");
					/** @noinspection PhpUndefinedFieldInspection */
					$set = $vidProvider::$id;
				}

				$fullep = $this->_episode->twoparter ? false : true;
				if ($part === 1 && $this->_episode->twoparter && isset($_POST["{$PostKey}_full"])){
					$NextPart = $provider.'_'.($part+1);
					$_POST[$NextPart] = null;
					$fullep = true;
				}

				$videocount = $Database
					->whereEp($this->_episode)
					->where('provider', $provider)
					->where('part', $part)
					->count('episodes__videos');
				if ($videocount === 0){
					if (!empty($set))
						$Database->insert('episodes__videos', [
							'season' => $this->_episode->season,
							'episode' => $this->_episode->episode,
							'provider' => $provider,
							'part' => $part,
							'id' => $set,
							'fullep' => $fullep,
						]);
				}
				else {
					$Database
						->whereEp($this->_episode)
						->where('provider', $provider)
						->where('part', $part);
					if (empty($set))
						$Database->delete('episodes__videos');
					else $Database->update('episodes__videos', [
						'id' => $set,
						'fullep' => $fullep,
						'modified' => date('c'),
					]);
				}
			}
		}

		Response::success('Links updated', ['epsection' => Episodes::getVideosHTML($this->_episode)]);
	}

	public function videoData($params){
		if (!isset($_GET['action']))
			Response::fail('Missing action');

		switch ($_GET['action']){
			case 'get': $this->_getVideoData($params); break;
			case 'set': $this->_setVideoData($params); break;
			default: CoreUtils::notFound();
		}
	}

	public function brokenVideos($params){
		$this->_getEpisode($params);

		global $Database;

		/** @var $videos EpisodeVideo[] */
		$videos = $Database
			->whereEp($this->_episode)
			->get('episodes__videos');

		$removed = 0;
		foreach ($videos as $video){
			if (!$video->isBroken())
				continue;

			$removed++;
			$Database->whereEp($this->_episode)->where('provider', $video->provider)->where('id', $video->id)->delete('episodes__videos');
			Logs::logAction('video_broken', [
				'season' => $this->_episode->season,
				'episode' => $this->_episode->episode,
				'provider' => $video->provider,
				'id' => $video->id,
			]);
		}

		if ($removed === 0)
			return Response::success('No broken videos found under this '.($this->_episode->isMovie?'movie':'episode').'.');

		Response::success("$removed video link".($removed===1?' has':'s have').' been removed from the site. Thank you for letting us know.', [
			'epsection' => Episodes::getVideosHTML($this->_episode, NOWRAP),
		]);
	}

	private function _getGuideRelations($params){
		$this->_getEpisode($params);

		global $Database;

		$CheckTag = [];

		$EpTagIDs = Episodes::getTagIDs($this->_episode);
		if (empty($EpTagIDs))
			Response::fail('The episode has no associated tag(s)!');

		$TaggedAppearanceIDs = [];
		foreach ($EpTagIDs as $tid){
			$AppearanceIDs = $Database->where('tid',$tid)->get('tagged',null,'ponyid');
			foreach ($AppearanceIDs as $id)
				$TaggedAppearanceIDs[$id['ponyid']] = true;
		}

		$Appearances = $Database->where('ishuman', $this->_episode->isMovie)->where('"id" != 0')->orderBy('label','ASC')->get('appearances',null,'id,label');

		$Sorted = [
			'unlinked' => [],
			'linked' => [],
		];
		foreach ($Appearances as $a)
			$Sorted[isset($TaggedAppearanceIDs[$a['id']]) ? 'linked' : 'unlinked'][] = $a;

		Response::done($Sorted);
	}

	private function _setGuideRelations($params){
		$this->_getEpisode($params);

		global $Database;

		/** @var $AppearanceIDs int[] */
		$AppearanceIDs = (new Input('ids','int[]', [
			Input::IS_OPTIONAL => true,
			Input::CUSTOM_ERROR_MESSAGES => [
				Input::ERROR_MISSING => 'Missing appearance ID list',
				Input::ERROR_INVALID => 'Appearance ID list is invalid',
			]
		]))->out();

		$EpTagIDs = Episodes::getTagIDs($this->_episode);
		if (empty($EpTagIDs))
			Response::fail('The episode has no associated tag(s)!');
		$EpTagIDs = implode(',',$EpTagIDs);
		$Tags = $Database->where("tid IN ($EpTagIDs)")->orderByLiteral('char_length(name)','DESC')->getOne('tags','tid');
		$UseID = $Tags['tid'];

		if (!empty($AppearanceIDs)){
			foreach ($AppearanceIDs as $id){
				if (!$Database->where("tid IN ($EpTagIDs)")->where('ponyid', $id)->has('tagged'))
					@$Database->insert('tagged', ['tid' => $UseID, 'ponyid' => $id]);
			}
			$Database->where('ponyid NOT IN ('.implode(',',$AppearanceIDs).')');
		}
		$Database->where("tid IN ($EpTagIDs)")->delete('tagged');

		Response::done(['section' => Episodes::getAppearancesSectionHTML($this->_episode)]);
	}

	public function guideRelations($params){
		$action = $_GET['action'];

		switch ($action){
			case 'get': $this->_getGuideRelations($params); break;
			case 'set': $this->_setGuideRelations($params); break;
			default: CoreUtils::notFound();
		}
	}
}

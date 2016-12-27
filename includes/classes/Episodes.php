<?php

namespace App;

use App\Appearances;
use App\Models\Episode;
use App\Models\EpisodeVideo;
use App\Models\Post;

class Episodes {
	const TITLE_CUTOFF = 26;
	static $ALLOWED_PREFIXES = array(
		'Equestria Girls' => 'EQG',
	);

	/**
	 * Returns all episodes from the database, properly sorted
	 *
	 * @param int|int[]   $count
	 * @param string|null $where
	 *
	 * @return Episode|Episode[]
	 */
	static function get($count = null, $where = null){
		/** @var $ep Episode */
		global $Database;

		if (!empty($where))
			$Database->where($where);

		$Database->orderBy('season')->orderBy('episode')->where('season != 0');
		if ($count !== 1){
			$eps =  $Database->get('episodes',$count);
			foreach ($eps as &$ep)
				$ep = $ep->addAiringData();
			return $eps;
		}
		else {
			$ep = $Database->getOne('episodes');
			return $ep->addAiringData();
		}
	}

	const ALLOW_MOVIES = true;

	/**
	 * If an episode is a two-parter's second part, then returns the first part
	 * Otherwise returns the episode itself
	 *
	 * @param int  $episode
	 * @param int  $season
	 * @param bool $allowMovies
	 *
	 * @throws \Exception
	 *
	 * @return Episode|null
	 */
	static function getActual(int $season, int $episode, bool $allowMovies = false){
		global $Database;

		if (!$allowMovies && $season == 0)
			throw new \Exception('This action cannot be performed on movies');

		/**
		 * @var $Ep Episode
		 * @var $Part1 Episode
		 */
		$Ep = $Database->whereEp($season,$episode)->getOne('episodes');
		if (!empty($Ep))
			return $Ep->addAiringData();

		$Part1 = $Database->whereEp($season,$episode-1)->getOne('episodes');
		return !empty($Part1) && !empty($Part1->twoparter)
			? $Part1->addAiringData()
			: null;
	}

	/**
	 * Returns the latest episode
	 *
	 * @return Episode
	 */
	static function getLatest(){
		return self::get(1,"airs < NOW() + INTERVAL '24 HOUR' && season != 0");
	}

	static function removeTitlePrefix($title){
		global $PREFIX_REGEX;

		return $PREFIX_REGEX->replace('', $title);
	}

	static function shortenTitlePrefix($title){
		global $PREFIX_REGEX;

		if (!$PREFIX_REGEX->match($title, $match) || !isset(self::$ALLOWED_PREFIXES[$match[1]]))
			return $title;

		return self::$ALLOWED_PREFIXES[$match[1]].': '.self::removeTitlePrefix($title);
	}

	/**
	 * Loads the episode page
	 *
	 * @param null|int|Episode $force              If null: Parses $data and loads approperiate epaisode
	 *                                             If array: Uses specified arra as Episode data
	 * @param bool             $serverSideRedirect Handle redirection to the correct page on the server/client side
	 * @param Post             $LinkedPost         Linked post (when sharing)
	 */
	static function loadPage($force = null, $serverSideRedirect = true, Post $LinkedPost = null){
		global $Database, $CurrentEpisode;

		if ($force instanceof Episode)
			$CurrentEpisode = $force;
		else if (is_string($force)){
			$EpData = Episode::parseID($force);

			if ($EpData['season'] === 0){
				error_log("Attempted visit to $force from ".(!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'[unknown referrer]').', redirecting to /movie page');
				HTTP::redirect('/movie/'.$EpData['episode']);
			}

			$CurrentEpisode = empty($EpData)
				? self::getLatest()
				: self::getActual($EpData['season'], $EpData['episode']);
		}
		if (empty($CurrentEpisode))
			CoreUtils::notFound();

		$url = $CurrentEpisode->formatURL();
		if (isset($LinkedPost)){
			$url .= '#'.$LinkedPost->getID();
		}
		if ($serverSideRedirect)
			CoreUtils::fixPath($url);

		$js = array('imagesloaded.pkgd','jquery.ba-throttle-debounce','jquery.fluidbox','Chart','episode');
		if (Permission::sufficient('member'))
			$js[] = 'episode-manage';
		if (Permission::sufficient('staff')){
			$js[] = 'moment-timezone';
			$js[] = 'episodes-manage';
		}

		if (!$CurrentEpisode->isMovie){
			$PrevEpisode = $Database
				->where('no',$CurrentEpisode->no, '<')
				->where('season', 0, '!=')
				->orderBy('no','DESC')
				->getOne('episodes','season,episode,title,twoparter');
			$NextEpisode = $Database
				->where('no',$CurrentEpisode->no, '>')
				->where('season', 0, '!=')
				->orderBy('no','ASC')
				->getOne('episodes','season,episode,title,twoparter');
		}
		else {
			$PrevEpisode = $Database
				->where('season', 0)
				->where('episode',$CurrentEpisode->episode, '<')
				->orderBy('episode','DESC')
				->getOne('episodes','season,episode,title');
			$NextEpisode = $Database
				->where('season', 0)
				->where('episode',$CurrentEpisode->episode, '>')
				->orderBy('episode','ASC')
				->getOne('episodes','season,episode,title');
		}

		$heading = $CurrentEpisode->formatTitle();
		CoreUtils::loadPage(array(
			'title' => "$heading - Vector Requests & Reservations",
			'heading' => $heading,
			'view' => 'episode',
			'css' => 'episode',
			'js' => $js,
			'url' => $serverSideRedirect ? null : $url,
			'import' => [
				'CurrentEpisode' => $CurrentEpisode,
				'PrevEpisode' => $PrevEpisode,
				'NextEpisode' => $NextEpisode,
				'LinkedPost' => $LinkedPost,
			],
		));
	}

	/**
	 * Get user's vote for an episode
	 *
	 * Accepts a single array containing values
	 *  for the keys 'season' and 'episode'
	 * Return's the user's vote entry from the DB
	 *
	 * @param Episode $Ep
	 * @return array
	 */
	static function getUserVote($Ep){
		global $Database, $signedIn, $currentUser;
		if (!$signedIn) return null;
		return $Database
			->whereEp($Ep)
			->where('user', $currentUser->id)
			->getOne('episodes__votes');
	}

	/**
	 * Get video embed HTML for an episode
	 *
	 * @param Episode $Episode
	 *
	 * @return array
	 */
	static function getVideoEmbeds($Episode):array {
		global $Database;

		/** @var $EpVideos EpisodeVideo[] */
		$EpVideos = $Database
			->whereEp($Episode)
			->orderBy('provider', 'ASC')
			->orderBy('part', 'ASC')
			->get('episodes__videos');
		$Parts = 0;
		$embed = '';
		if (!empty($EpVideos)){
			$Videos = array();
			foreach ($EpVideos as $v)
				$Videos[$v->provider][$v->part] = $v;
			// YouTube embed preferred
			$Videos = !empty($Videos['yt']) ? $Videos['yt'] : $Videos['dm'];

			$Parts = count($Videos);
			foreach ($Videos as $v)
				$embed .= "<div class='responsive-embed".($Episode->twoparter && $v->part!==1?' hidden':'')."'>".VideoProvider::getEmbed($v)."</div>";
		}
		return [
			'parts' => $Parts,
			'html' => $embed
		];
	}

	static
		$VIDEO_PROVIDER_NAMES = array(
			'yt' => 'YouTube',
			'dm' => 'Dailymotion',
		),
		$PROVIDER_BTN_CLASSES = array(
			'yt' => 'red typcn-social-youtube',
			'dm' => 'darkblue typcn-video',
		);
	/**
	 * Renders the HTML of the "Watch the Episode" section along with the buttons/links
	 *
	 * @param Episode $Episode
	 * @param bool    $wrap
	 *
	 * @return string
	 */
	static function getVideosHTML($Episode, bool $wrap = WRAP):string {
		global $Database;

		$HTML = '';
		/** @var $Videos EpisodeVideo[] */
		$Videos = $Database
			->whereEp($Episode)
			->orderBy('provider', 'ASC')
			->orderBy('part', 'ASC')
			->get('episodes__videos');

		if (!empty($Videos)){
			$fullep = $Episode->twoparter ? 'Full episode' : '';
			if (count($Videos) === 1 && $Videos[0]->provider === 'yt'){
				$airtime = strtotime($Episode->airs);
				$modified = $Videos[0]->modified;
				if (!empty($modified) && $airtime > strtotime($modified)){
					$fullep = 'Livestream';
					$Episode = $Episode->addAiringData();
					if ($Episode->aired)
						$fullep .= ' recording';
					if (!$Episode->twoparter)
						$fullep = " ($fullep)";
				}
			}

			$HTML = ($wrap ? "<section class='episode'>" : '')."<h2>Watch the ".($Episode->isMovie?'Movie':'Episode')."</h2><p class='align-center actions'>";
			foreach ($Videos as $v){
				$url = VideoProvider::getEmbed($v, VideoProvider::URL_ONLY);
				$partText = $Episode->twoparter ? (
					!$v->fullep
					? " (Part {$v->part})"
					: " ($fullep)"
				) : $fullep;
				$HTML .= "<a class='btn typcn ".self::$PROVIDER_BTN_CLASSES[$v->provider]."' href='$url' target='_blank'>".self::$VIDEO_PROVIDER_NAMES[$v->provider]."$partText</a>";
			}
			$HTML .= "<button class='green typcn typcn-eye showplayers'>Show on-site player</button><button class='orange typcn typcn-flag reportbroken'>Report broken video</button></p>";
			if ($wrap)
				$HTML .= '</section>';
		}

		return $HTML;
	}

	/**
	 * Get the <tbody> contents for the episode list table
	 *
	 * @param Episode[]|null $Episodes
	 * @param bool           $areMovies
	 *
	 * @return string
	 */
	static function getTableTbody($Episodes = null, bool $areMovies = false):string {
		if (empty($Episodes))
			return "<tr class='empty align-center'><td colspan='3'><em>There are no ".($areMovies?'movies':'episodes')." to display</em></td></tr>";

		$Body = '';
		$PathStart = '/episode/';
		$displayed = false;
		foreach ($Episodes as $Episode) {
			$adminControls = Permission::insufficient('staff') ? '' : <<<HTML
<span class='admincontrols'>
<button class='edit-episode typcn typcn-pencil blue' title='Edit episode'></button>
<button class='delete-episode typcn typcn-times red' title='Delete episode'></button>
</span>
HTML;
			$SeasonEpisode = $DataID = '';
			$title = $Episode->formatTitle(AS_ARRAY);
			if (!$Episode->isMovie){
				$href = $PathStart.$title['id'];
				if ($Episode->twoparter)
					$title['episode'] .= '-'.(intval($title['episode'],10)+1);
				$SeasonEpisode = <<<HTML
			<td class='season' rowspan='2'>{$title['season']}</td>
			<td class='episode' rowspan='2'>{$title['episode']}</td>
HTML;

			}
			else {
				$href = $Episode->formatURL();
				$SeasonEpisode = "<td class='episode' rowspan='2'>{$title['episode']}</td>";
			}
			$DataID = " data-epid='{$title['id']}'";

			$star = '';
			if ($Episode->isLatest()){
				$displayed = true;
				$star = '<span class="typcn typcn-home" title="Curently visible on the homepage"></span> ';
			}
			$Episode->addAiringData();
			if (!$Episode->aired)
				$star .= '<span class="typcn typcn-media-play-outline" title="'.($Episode->isMovie?'Movie':'Episode').' didn\'t air yet, voting disabled"></span>&nbsp;';

			$airs = Time::tag($Episode->airs, Time::TAG_EXTENDED, Time::TAG_NO_DYNTIME);

			$Body .= <<<HTML
	<tr$DataID>
		$SeasonEpisode
		<td class='title'>$star<a href="$href">{$title['title']}</a>$adminControls</td>
	</tr>
	<tr><td class='airs'>$airs</td></tr>
HTML;
		}
		return $Body;
	}

	/**
	 * Render upcoming episode HTML
	 *
	 * @param bool $wrap Whether to output the wrapper elements
	 *
	 * @return string
	 */
	static function getSidebarUpcoming($wrap = WRAP){
		global $Database, $PREFIX_REGEX;
		/** @var $Upcoming Episode[] */
		$Upcoming = $Database->where('airs > NOW()')->orderBy('airs', 'ASC')->get('episodes');
		if (empty($Upcoming)) return;

		$HTML = '';
		foreach ($Upcoming as $Episode){
			$airtime = strtotime($Episode->airs);
			$airs = date('c', $airtime);
			$month = date('M', $airtime);
			$day = date('j', $airtime);
			$diff = Time::difference(time(), $airtime);

			$time = 'in ';
			if ($diff['time'] < Time::IN_SECONDS['month']){
				$tz = "(".date('T', $airtime).")";
				if (!empty($diff['day']))
					$time .=  "{$diff['day']} day".($diff['day']!==1?'s':'').' & ';
				if (!empty($diff['hour']))
					$time .= "{$diff['hour']}:";
				foreach (array('minute','second') as $k)
					$diff[$k] = CoreUtils::pad($diff[$k]);
				$time = "<time datetime='$airs'>$time{$diff['minute']}:{$diff['second']} $tz</time>";
			}
			else $time = Time::tag($Episode->airs);

			$title = !$Episode->isMovie
				? $Episode->title
				: (
					$PREFIX_REGEX->match($Episode->title)
					? Episodes::shortenTitlePrefix($Episode->title)
					: "Movie: {$Episode->title}"
				);

			$HTML .= "<li><div class='calendar'><span class='top'>$month</span><span class='bottom'>$day</span></div>".
				"<div class='meta'><span class='title'>$title</span><span class='time'>Airs $time</span></div></li>";
		}
		return $wrap ? "<section id='upcoming'><h2>Upcoming episodes</h2><ul>$HTML</ul></section>" : $HTML;
	}

	/**
	 * Render episode voting HTML
	 *
	 * @param Episode $Episode
	 *
	 * @return string
	 */
	static function getSidebarVoting(Episode $Episode):string {
		$thing = $Episode->isMovie ? 'movie' : 'episode';
		if (!$Episode->aired)
			return "<p>Voting will start ".Time::tag($Episode->willair).", after the $thing had aired.</p>";
		global $Database, $signedIn, $currentUser;
		$HTML = '';

		if (empty($Episode->score))
			$Episode->updateScore();

		$Score = preg_replace(new RegExp('^(\d+)\.0+$'),'$1',number_format($Episode->score,1));
		$ScorePercent = round(($Score/5)*1000)/10;

		$HTML .= '<p>'.(!empty($Score) ? "This $thing is rated $Score/5 (<a class='detail'>Details</a>)" : 'Nopony voted yet.').'</p>';
		if ($Score > 0)
			$HTML .= "<img src='/muffin-rating?w=$ScorePercent' id='muffins' alt='muffin rating svg'>";

		$UserVote = Episodes::getUserVote($Episode);
		if (empty($UserVote)){
			$HTML .= "<br><p>What did <em>you</em> think about the $thing?</p>";
			if ($signedIn)
				$HTML .= "<button class='blue rate typcn typcn-star'>Cast your vote</button>";
			else $HTML .= "<p><em>Sign in above to cast your vote!</em></p>";
		}
		else $HTML .= "<p>Your rating: ".CoreUtils::makePlural('muffin', $UserVote['vote'], PREPEND_NUMBER).'</p>';

		return $HTML;
	}

	/**
	 * Get a list of IDs for tags related to the episode
	 *
	 * @param Episode $Episode
	 *
	 * @return int[]
	 */
	static function getTagIDs(Episode $Episode):array {
		global $Database;

		if ($Episode->isMovie){
			$MovieTagIDs = [];
			$MovieTag = $Database->where('name',"movie#$Episode->episode")->where('type','ep')->getOne('tags','tid');
			if (!empty($MovieTag['tid']))
				$MovieTagIDs[] = $MovieTag['tid'];
			return $MovieTagIDs;
		}
		else {
			$sn = CoreUtils::pad($Episode->season);
			$en = CoreUtils::pad($Episode->episode);
			$EpTagIDs = array();
			$EpTagPt1 = $Database->where('name',"s{$sn}e{$en}")->where('type','ep')->getOne('tags','tid');
			if (!empty($EpTagPt1))
				$EpTagIDs[] = $EpTagPt1['tid'];
			if ($Episode->twoparter){
				$next_en = CoreUtils::pad($Episode->episode+1);
				$EpTagPt2 = $Database->rawQuery("SELECT tid FROM tags WHERE name IN ('s{$sn}e{$next_en}', 's{$sn}e{$en}-{$next_en}') && type = 'ep'");
				foreach ($EpTagPt2 as $t)
					$EpTagIDs[] = $t['tid'];
			}
			return $EpTagIDs;
		}
	}

	static function getAppearancesSectionHTML(Episode $Episode):string {
		global $Database, $Color;

		$HTML = '';
		$EpTagIDs = Episodes::getTagIDs($Episode);
		if (!empty($EpTagIDs)){
			$TaggedAppearances = $Database->rawQuery(
				"SELECT p.id, p.label, p.private
				FROM tagged t
				LEFT JOIN appearances p ON t.ponyid = p.id
				WHERE t.tid IN (".implode(',',$EpTagIDs).") && p.ishuman = ?
				ORDER BY p.label",array($Episode->isMovie));

			if (!empty($TaggedAppearances)){
				$hidePreviews = UserPrefs::get('ep_noappprev');
				$pages = CoreUtils::makePlural('page', count($TaggedAppearances));
				$HTML .= "<section class='appearances'><h2>Related <a href='/cg'>$Color Guide</a> $pages</h2>";
				$LINKS = '<ul>';
				$isStaff = Permission::sufficient('staff');
				foreach ($TaggedAppearances as $p){
					$safeLabel = Appearances::getSafeLabel($p);
					if (Appearances::isPrivate($p, true)){
						$preview = "<span class='typcn typcn-".($isStaff?'lock-closed':'time')." color-".(($isStaff?'orange':'darkblue'))."'></span> ";
					}
					else {
						if ($hidePreviews)
							$preview = '';
						else {
							$preview = Appearances::getPreviewURL($p);
							$preview = "<img src='$preview' class='preview'>";
						}
					}
					$LINKS .= "<li><a href='/cg/v/{$p['id']}-$safeLabel'>$preview{$p['label']}</a></li>";
				}
				$HTML .= "$LINKS</ul></section>";
			}
		}
		return $HTML;
	}

	/**
	 * Gets the number of posts bound to an episode
	 *
	 * @param Episode $Episode
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	static function getPostCount($Episode){
		global $Database;

		return $Episode->getPostCount();
	}

	static function validateSeason($allowMovies = false){
		return (new Input('season','int',array(
			Input::IN_RANGE => [$allowMovies ? 0 : 1, 8],
			Input::CUSTOM_ERROR_MESSAGES => array(
				Input::ERROR_MISSING => 'Season number is missing',
				Input::ERROR_INVALID => 'Season number (@value) is invalid',
				Input::ERROR_RANGE => 'Season number must be between @min and @max',
			)
		)))->out();
	}
	static function validateEpisode($optional = false, $EQG = false){
		$FieldName = $EQG ? 'Overall movie number' : 'Episode number';
		return (new Input('episode','int',array(
			Input::IS_OPTIONAL => $optional,
			Input::IN_RANGE => [1,26],
			Input::CUSTOM_ERROR_MESSAGES => array(
				Input::ERROR_MISSING => "$FieldName is missing",
				Input::ERROR_INVALID => "$FieldName (@value) is invalid",
				Input::ERROR_RANGE => "$FieldName must be between @min and @max",
			)
		)))->out();
	}
}

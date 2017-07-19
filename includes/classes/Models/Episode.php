<?php

namespace App\Models;

use ActiveRecord\DateTime;
use App\Auth;
use App\CoreUtils;
use App\DB;
use App\Episodes;
use App\RegExp;

/**
 * @property int            $season
 * @property int            $episode
 * @property int            $no
 * @property int            $willairts
 * @property string         $title
 * @property DateTime       $posted
 * @property string         $posted_by
 * @property string         $airs
 * @property string         $willair
 * @property string         $notes
 * @property bool           $is_movie   (Via magic method)
 * @property string|null    $score      (Via magic method)
 * @property bool           $twoparter  (Via magic method)
 * @property bool           $displayed  (Via magic method)
 * @property bool           $aired      (Via magic method)
 * @property EpisodeVideo[] $videos     (Via relations)
 * @property User           $poster     (Via relations)
 * @method static Episode find_by_season_and_episode(int $season, int $episode)
 */
class Episode extends NSModel {
	static $primary_key = ['season','episode'];

	public static $has_many = [
		['videos', 'class' => 'EpisodeVideo', 'foreign_key' => ['season','episode'], 'order' => 'provider asc, part asc']
	];
	public static $belongs_to = [
		['poster', 'class' => 'User', 'foreign_key' => 'posted_by'],
	];

	public function get_is_movie():bool {
		return $this->season === 0;
	}

	private function _normalizeScore($value):string {
		return is_numeric($value) ? number_format($value,1) : '0.0';
	}

	public function get_score():string {
		$attr = $this->read_attribute('score');
		return $this->_normalizeScore($attr);
	}

	public function set_score($score){
		$this->assign_attribute('score', $this->_normalizeScore($score));
	}

	public function get_displayed(){
		return $this->isDisplayed();
	}

	public function get_willairts(){
		return $this->willHaveAiredBy();
	}

	public function get_aired(){
		return $this->hasAired();
	}

	public function get_willair(){
		return gmdate('c', $this->willairts);
	}

	/**
	 * @param array $o
	 *
	 * @return string
	 */
	public function getID(array $o = []):string {
		if ($this->is_movie)
			return 'Movie'.(!empty($o['append_num'])?'#'.$this->episode:'');

		$episode = $this->episode;
		$season = $this->season;

		if (empty($o['pad'])){
			if ($this->twoparter)
				$episode = $episode.'-'.($episode+1);
			return "S{$season}E{$episode}";
		}

		$episode = CoreUtils::pad($episode).($this->twoparter ? '-'.CoreUtils::pad($episode+1) : '');
		$season = CoreUtils::pad($season);

		return "S{$season} E{$episode}";
	}

	/**
	 * Gets the number of posts bound to an episode
	 *
	 * @return int
	 */
	public function getPostCount():int {
		return (int) DB::$instance->querySingle(
			'SELECT SUM(cnt) as postcount FROM (
				SELECT count(*) as cnt FROM requests WHERE season = :season AND episode = :episode
				UNION ALL
				SELECT count(*) as cnt FROM reservations WHERE season = :season AND episode = :episode
			) t',
			[':season' => $this->season, ':episode' => $this->episode]
		)['postcount'];
	}

	/**
	 * @return string
	 */
	public function movieSafeTitle():string {
		return (new RegExp('-{2,}'))->replace('-', (new RegExp('[^a-z]','i'))->replace('-', $this->title));
	}

	/**
	 * @param Episode $ep
	 *
	 * @return bool
	 */
	public function is(Episode $ep):bool {
		return $this->season === $ep->season
			&& $this->episode === $ep->episode;
	}

	public function isLatest():bool {
		$latest = Episodes::getLatest();
		return $this->is($latest);
	}

	/**
	 * @param int $now Current time (for testing purposes)
	 *
	 * @return bool Indicates whether the episode is close enough to airing to be the home page
	 */
	public function isDisplayed($now = null):bool {
		$airtime = strtotime($this->airs);
		return strtotime('-24 hours', $airtime) < ($now ?? time());
	}

	/**
	 * @return int The timestamp after which the episode is considered to have aired & voting can be enabled
	 */
	public function willHaveAiredBy():int {
		$airtime = strtotime($this->airs);
		return strtotime('+'.($this->is_movie?'2 hours':((!$this->twoparter?30:60).' minutes')), $airtime);
	}

	/**
	 * @param int $now Current time (for testing purposes)
	 *
	 * @return bool True if willHaveAiredBy() is in the past
	 */
	public function hasAired($now = null):bool {
		return $this->willairts < ($now ?? time());
	}

	/**
	 * Turns an 'episode' database row into a readable title
	 *
	 * @param bool        $returnArray Whether to return as an array instead of string
	 * @param string      $arrayKey
	 * @param bool        $append_num  Append overall # to ID
	 *
	 * @return string|array
	 */
	public function formatTitle($returnArray = false, $arrayKey = null, $append_num = true){
		if ($returnArray === AS_ARRAY) {
			$arr = [
				'id' => $this->getID(['append_num' => $append_num]),
				'season' => $this->season ?? null,
				'episode' => $this->episode ?? null,
				'title' => isset($this->title) ? CoreUtils::escapeHTML($this->title) : null,
			];

			if (!empty($arrayKey))
				return $arr[$arrayKey] ?? null;
			else return $arr;
		}

		if ($this->is_movie)
			return $this->title;

		return $this->getID(['pad' => true]).': '.$this->title;
	}

	public function toURL(){
		if (!$this->is_movie)
			return '/episode/'.$this->formatTitle(AS_ARRAY,'id');
		return "/movie/{$this->episode}".(!empty($this->title)?'-'.$this->movieSafeTitle():'');
	}

	public function updateScore(){
		$Score = DB::$instance->whereEp($this)->disableAutoClass()->getOne('episode_votes','AVG(vote) as score');
		$this->score = !empty($Score['score']) ? $Score['score'] : 0;
		$this->save();
	}

	/**
	 * Extracts the season and episode numbers from the episode ID string
	 * Examples:
	 *   "S1E1" => {season:1,episode:1}
	 *   "S01E01" => {season:1,episode:1}
	 *   "S1E1-2" => {season:1,episode:1,twoparter:true}
	 *   "S01E01-02" => {season:1,episode:1,twoparter:true}
	 *
	 * @param string $id
	 * @return null|array
	 */
	public static function parseID($id){
		if (empty($id))
			return null;

		global $EPISODE_ID_REGEX, $MOVIE_ID_REGEX;
		if (preg_match($EPISODE_ID_REGEX, $id, $match))
			return [
				'season' => intval($match[1], 10),
				'episode' => intval($match[2], 10),
				'twoparter' => !empty($match[3]),
			];
		else if (preg_match($MOVIE_ID_REGEX, $id, $match))
			return [
				'season' => 0,
				'episode' => intval($match[1], 10),
				'twoparter' => false,
			];
		else return null;
	}

	/**
	 * Gets the rating given to the episode by the user, or null if not voted
	 *
	 * @param User $user
	 *
	 * @return EpisodeVote|null
	 */
	public function getUserVote(?User $user = null):?EpisodeVote {
		if ($user === null && Auth::$signed_in)
			$user = Auth::$user;
		return EpisodeVote::find_for($this, $user);
	}

	const
		PREVIOUS = '<',
		NEXT = '>';
	/**
	 * @param string $dir Expects self::PREVIOUS or self::NEXT
	 *
	 * @return Episode|null
	 */
	private function _getAdjacent($dir):?Episode {
		$is = $this->is_movie ? '=' : '!=';
		return Episode::find('first', [
			'conditions' => [
				"season $is 0 AND no $dir ?",
				$this->no
			],
			'order' => 'no '.($dir === self::NEXT ? 'asc' : 'desc'),
			'limit' => 1,
		]);
	}

	/**
	 * Get the previous episode based on overall episode number
	 * @return Episode|null
	 */
	public function getPrevious():?Episode {
		return $this->_getAdjacent(self::PREVIOUS);
	}

	/**
	 * Get the previous episode based on overall episode number
	 * @return Episode|null
	 */
	public function getNext():?Episode {
		return $this->_getAdjacent(self::NEXT);
	}

	/**
	 * Get a list of IDs for tags related to the episode
	 *
	 * @return int[]
	 */
	public function getTagIDs():array {
		if ($this->is_movie){
			$MovieTagIDs = [];
			/** @var $MovieTag Tag */
			$MovieTag = DB::$instance->where('name',"movie{$this->episode}")->where('type','ep')->getOne('tags','id');
			if (!empty($MovieTag->id))
				$MovieTagIDs[] = $MovieTag->id;
			return $MovieTagIDs;
		}

		$sn = CoreUtils::pad($this->season);
		$en = CoreUtils::pad($this->episode);
		$EpTagIDs = [];
		/** @var $EpTagPt1 array */
		$EpTagPt1 = DB::$instance->disableAutoClass()->where('name',"s{$sn}e{$en}")->where('type','ep')->getOne('tags','id');
		if (!empty($EpTagPt1))
			$EpTagIDs[] = $EpTagPt1['id'];
		if ($this->twoparter){
			$next_en = CoreUtils::pad($this->episode+1);
			/** @var $EpTagPt2 array */
			$EpTagPt2 = DB::$instance->query("SELECT id FROM tags WHERE name IN (?, ?) AND type = 'ep'",["s{$sn}e{$next_en}", "s{$sn}e{$en}-{$next_en}"]);
			foreach ($EpTagPt2 as $t)
				$EpTagIDs[] = $t['id'];
		}
		return $EpTagIDs;
	}
}

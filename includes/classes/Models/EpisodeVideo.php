<?php

namespace App\Models;

use App\CoreUtils;
use App\JSON;
use App\Response;
use App\Time;
use App\VideoProvider;

/**
 * @property int     $season
 * @property int     $episode
 * @property int     $part
 * @property bool    $fullep
 * @property string  $provider
 * @property string  $id
 * @property string  $modified
 * @property string  $not_broken_at
 * @property Episode $ep
 */
class EpisodeVideo extends NSModel {
	public static $primary_key = ['season', 'episode', 'provider', 'part'];

	public static $belongs_to = [
		['ep', 'class' => 'Episode', 'foreign_key' => ['season','episode']],
	];

	public function isBroken():bool {
		if (isset($this->not_broken_at)){
			$nb = strtotime($this->not_broken_at);
			if ($nb+(Time::IN_SECONDS['hour']*2) > time())
				return false;
		}

		switch ($this->provider){
			case 'yt':
				$url = VideoProvider::getEmbed($this, VideoProvider::URL_ONLY);
				$broken = !CoreUtils::isURLAvailable('http://www.youtube.com/oembed?url='.urlencode($url));
			break;
			case 'dm':
				$broken = !CoreUtils::isURLAvailable("https://api.dailymotion.com/video/{$this->id}");
			break;
			default:
				throw new \RuntimeException("No breakage check defined for provider {$this->provider}");
		}

		if (!$broken){
			$this->not_broken_at = date('c');
			$this->save();
		}

		return $broken;
	}
}

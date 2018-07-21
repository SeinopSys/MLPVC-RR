<?php

namespace App\Controllers;

use App\CoreUtils;
use App\Episodes;
use App\Pagination;
use App\Permission;
use App\Models\Episode;

class ShowController extends Controller {
	public function index(){
		$base_path = '/show';
		$episodes_pagination = new Pagination($base_path, 8, Episode::count(['conditions' => 'season != 0']), 'ep');
		$movies_pagination = new Pagination($base_path, 8, Episode::count(['conditions' => 'season = 0']), 'movie');

		$episodes = Episodes::get($episodes_pagination->getLimit());
		$movies = Episodes::get($movies_pagination->getLimit(), 'season = 0', true);

		$path = $episodes_pagination->toURI();
		$path->append_query_raw($movies_pagination->getPageQueryString());
		CoreUtils::fixPath($path);
		$heading = 'Episodes & Movies';

		$settings = [
			'heading' => $heading,
			'title' => $heading,
			'css' => [true],
			'js' => ['paginate', true],
			'import' => [
				'episodes_pagination' => $episodes_pagination,
				'movies_pagination' => $movies_pagination,
				'episodes' => $episodes,
				'movies' => $movies,
			],
		];
		if (Permission::sufficient('staff')){
			$settings['js'] = array_merge(
				$settings['js'],
				['moment-timezone', 'pages/show/index-manage']
			);
			global $EP_TITLE_REGEX;
			$settings['import']['export'] = [
				'EP_TITLE_REGEX' => $EP_TITLE_REGEX,
			];
		}
		CoreUtils::loadPage(__METHOD__, $settings);
	}
}

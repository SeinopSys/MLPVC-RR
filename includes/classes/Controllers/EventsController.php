<?php

namespace App\Controllers;

use App\CoreUtils;
use App\Events;
use App\Pagination;
use App\Permission;
use App\Models\Event;

class EventsController extends Controller {
	public $do = 'events';

	public function list(){
		$Pagination = new Pagination('events', 20, Event::count());

		CoreUtils::fixPath("/events/{$Pagination->page}");
		$heading = 'Events';
		$title = "Page $Pagination->page - $heading";

		$Events = Event::find('all', $Pagination->getAssocLimit());

		$Pagination->respondIfShould(Events::getListHTML($Events, NOWRAP), '#event-list');

		$js = ['paginate'/*, $this->do*/];
		if (Permission::sufficient('staff'))
			$js[] = "{$this->do}-manage";

		CoreUtils::loadPage([
			'title' => $title,
			'heading' => $heading,
			'js' => $js,
			'css' => ['events'],
			'import' => [
				'Events' => $Events,
				'Pagination' => $Pagination,
				'PRINTABLE_ASCII_PATTERN' => PRINTABLE_ASCII_PATTERN,
			],
		], $this);
	}
}

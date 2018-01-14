<?php

namespace App;
use ActiveRecord\SQLBuilder;
use HtmlGenerator\HtmlTag;
use Peertopark\UriBuilder;

/**
 * Class for writing out complex pagination HTML
 *  derived from http://codereview.stackexchange.com/a/10292/21877
 */
class Pagination {
	public $maxPages, $page, $itemsPerPage;
	public $_context = 2, $_wrap, $basePath;
	/**
	 * @var string
	 */
	private $_noconflict;

	/**
	 * Creates an instance of the class and return the generated HTML
	 *
	 * @param string $basePath     The starting path of ech paginated page without the page number
	 * @param int    $ItemsPerPage Number of items to display on a single page
	 * @param int    $EntryCount   Number of available entries
	 * @param string $noconflict   Specify a name to use for the query string parameter
	 */
	public function __construct(string $basePath, int $ItemsPerPage, ?int $EntryCount = null, string $noconflict = ''){
		$this->itemsPerPage = $ItemsPerPage;
		$this->basePath = $basePath;
		$this->_noconflict = $noconflict;
		$this->page = 1;
		$this->guessPage();

		if ($EntryCount !== null)
			$this->calcMaxPages($EntryCount);
	}

	private function guessPage(){
		$page = $_GET["{$this->_noconflict}page"] ?? null;
		if ($page === null){
			$uri = explode('/', $_SERVER['REQUEST_URI']);
			$page = array_pop($uri);
		}
		if (is_numeric($page))
			$this->page = max((int) $page, 1);
	}

	/**
	 * Set a specific page as the currrent
	 *
	 * @param int $page
	 *
	 * @return self
	 */
	public function forcePage(int $page){
		$this->page = max($page, 1);

		return $this;
	}

	/**
	 * Calculate the number of maximum possible pages
	 *
	 * @param int $EntryCount
	 *
	 * @return self
	 */
	public function calcMaxPages(int $EntryCount){
		$this->maxPages = (int) max(1, ceil($EntryCount/$this->itemsPerPage));
		if ($this->page > $this->maxPages)
			$this->page = $this->maxPages;

		return $this;
	}

	/**
	 * Collect page numbers for pagination
	 *
	 * @return array
	 * @throws \RuntimeException
	 */
	private function _getLinks(){
		if ($this->maxPages === null)
			throw new \RuntimeException('$this->maxPages must be defined');

		return array_unique(
			array_merge(
				[1],
				range(
					max($this->page - $this->_context, 1),
					min($this->page + $this->_context, $this->maxPages)
				),
				[$this->maxPages]
			)
		);
	}

	private function _makeLink($i){
		$href = new UriBuilder("/{$this->basePath}");
		$href->append_query_raw($this->getPageQueryString($i));
		return $href->build_http_string();
	}

	private function _makeItem(int $i, &$currentIndex = null, $nr = null){
		$current = $i === (int) $this->page;
		if ($currentIndex !== null && $current)
			$currentIndex = $nr;
		return '<li>'.(
			!$current
			? HtmlTag::createElement('a')->set('href', $this->_makeLink($i))->text($i)
			: "<strong>$i</strong>"
		).'</li>';
	}

	public function toHTML(bool $wrap = WRAP):string {
		if ($this->maxPages === null){
			CoreUtils::error_log(__METHOD__.": maxPages peroperty must be defined\nData: ".var_export($this, true)."\nTrace:\n".(new \RuntimeException())->getTraceAsString());
			return '';
		}

		if (!($this->page === 1 && $this->maxPages === 1)){
			$Items = [];
			$previousPage = 0;
			$nr = 0;
			$currentIndex = 0;

			if ($this->maxPages < 7){
				for ($i = 1; $i <= $this->maxPages; $i++){
					$Items[$nr] = $this->_makeItem($i, $currentIndex, $nr++);
					$nr++;
				}
			}
			else {
				/** @noinspection MagicMethodsValidityInspection */
				foreach ($this->_getLinks() as $i) {
					if ($i !== min($previousPage + 1, $this->maxPages)){
						$diff = $i - ($previousPage + 1);
						if ($diff > 1){
							$item = HtmlTag::createElement('li')->set('class','spec');
							$item->addElement('a')->text("\u{2026}");
							$item->attr('data-baseurl', $this->_makeLink('*'));
						}
						else $item = $this->_makeItem($previousPage+1);
						$Items[$nr++] = $item;
					}
					$previousPage = $i;

					$Items[$nr] = $this->_makeItem($i, $currentIndex, $nr);
					$nr++;
				}
			}

			$Items = implode('',$Items);
		}
		else $Items = '';

		$path = CoreUtils::aposEncode($this->basePath);

		return $wrap ? "<ul class='pagination' data-for='$path'>$Items</ul>" : $Items;
	}

	/**
	 * Write the pagination links
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->toHTML();
	}

	public function toElastic(){
		$limit = $this->getLimit();
		return [
			'from' => $limit[0],
			'size' => $limit[1],
		];
	}

	/**
	 * Creates the LIMIT array that can be used with PostgresDb's get() method
	 *
	 * @return int[]
	 */
	public function getLimit(){
		return [ ($this->page-1)*$this->itemsPerPage, $this->itemsPerPage ];
	}

	/**
	 * Creates the associative array that can be used ActiveRecord's find() method
	 *
	 * @return array
	 */
	public function getAssocLimit(){
		$arr = $this->toElastic();
		return [ 'offset' => $arr['from'], 'limit' => $arr['size'] ];
	}

	/**
	 * Apply the limit and offset attributes on an SQLBuilder
	 *
	 * @param SQLBuilder $query
	 */
	public function applyAssocLimit(SQLBuilder $query){
		$assoc = $this->getAssocLimit();
		foreach ($assoc as $k => $v)
			$query->{$k} = $v;
	}

	/**
	 * Converts GetLimit()'s output to a string
	 *
	 * @return string
	 */
	public function getLimitString():string {
		$limit = $this->getLimit();
		return "LIMIT $limit[1] OFFSET $limit[0]";
	}

	/**
	 * Returns the raw query string parameter for the page
	 *
	 * @param int|null $page Page number, or current page if not specified
	 *
	 * @return string
	 */
	public function getPageQueryString($page = null):string {
		$pagenum = $page ?? $this->page;
		if ($pagenum === 1)
			$pagenum = CoreUtils::FIXPATH_EMPTY;
		return "{$this->_noconflict}page=$pagenum";
	}
}

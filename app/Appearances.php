<?php

namespace App;

use App\Models\Appearance;
use App\Models\Notification;
use Elasticsearch\Common\Exceptions\Missing404Exception as ElasticMissing404Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException as ElasticNoNodesAvailableException;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException as ElasticServerErrorResponseException;

class Appearances {
	public const COUNT_COL = 'COUNT(*) as cnt';
	public const PCG_APPEARANCE_MAKE_DISABLED = 'You are not allowed to create personal color guide appearances';

	/**
	 * @param bool|null $EQG
	 * @param int|int[] $limit
	 * @param string    $userid
	 * @param string    $cols
	 *
	 * @return Appearance[]
	 */
	public static function get(?bool $EQG, $limit = null, ?string $userid = null, ?string $cols = null){
		if ($userid !== null)
			DB::$instance->where('owner_id', $userid);
		else {
			DB::$instance->where('owner_id IS NULL');
			self::_order();
			if ($EQG !== null)
				DB::$instance->where('ishuman', $EQG)->where('id',0,'!=');
		}
		if ($cols === self::COUNT_COL)
			DB::$instance->disableAutoClass();

		return DB::$instance->get('appearances', $limit, $cols);
	}

	/**
	 * Order appearances
	 *
	 * @param string $dir
	 */
	private static function _order($dir = 'ASC'){
		DB::$instance->orderByLiteral('CASE WHEN "order" IS NULL THEN 1 ELSE 0 END', $dir)
			->orderBy('"order"', $dir)
			->orderBy('id', $dir);
	}

	/**
	 * Sort appearances based on tags
	 *
	 * @param Appearance[] $Appearances
	 * @param bool         $EQG
	 * @param bool         $simpleArray
	 *
	 * @return array
	 */
	public static function sort($Appearances, bool $EQG, bool $simpleArray = false){
		$GroupTagIDs = array_keys(CGUtils::GROUP_TAG_IDS_ASSOC[$EQG?'eqg':'pony']);
		$Sorted = [];
		$Tagged = [];
		$_tagged = DB::$instance->where('tag_id IN ('.implode(',',$GroupTagIDs).')')->orderBy('appearance_id')->get('tagged');
		foreach ($_tagged as $row)
			$Tagged[$row->appearance_id][] = $row->tag_id;
		foreach ($Appearances as $p){
			if (!empty($Tagged[$p->id])){
				if (\count($Tagged[$p->id]) > 1)
					usort($Tagged[$p->id],function($a,$b) use ($GroupTagIDs){
						return array_search($a, $GroupTagIDs, true) - array_search($b, $GroupTagIDs, true);
					});
				$tid = $Tagged[$p->id][0];
			}
			else $tid = -1;
			$Sorted[$tid][] = $p;
		}
		if ($simpleArray){
			$idArray = [];
			foreach (CGUtils::GROUP_TAG_IDS_ASSOC[$EQG?'eqg':'pony'] as $Category => $CategoryName){
				if (empty($Sorted[$Category]))
					continue;
				/** @var $Sorted Appearance[][] */
				foreach ($Sorted[$Category] as $p)
					$idArray[] = $p->id;
			}
			return $idArray;
		}
		else return $Sorted;
	}

	/**
	 * @param string|int[] $ids
	 */
	public static function reorder($ids){
		if (empty($ids))
			return;

		$list = array_flip(\is_string($ids) ? explode(',', $ids) : $ids);
		/** @var $appearances Appearance[] */
		$appearances = DB::$instance->where('id', $list)->get(Appearance::$table_name);
		foreach ($appearances as $app){
			if (!isset($list[$app->id]))
				continue;

			$app->order = $list[$app->id];
			if (!$app->save())
				Response::fail("Updating appearance #{$app->id} failed, process halted");

			$app->updateIndex();
		}
	}

	/**
	 * @param bool $EQG
	 */
	public static function getSortReorder($EQG){
		if ($EQG)
			return;
		self::reorder(self::sort(self::get($EQG,null,null,'id'), $EQG, SIMPLE_ARRAY));
	}

	public static function reindex(){
		$elasticClient = CoreUtils::elasticClient();
		try {
			$elasticClient->indices()->delete(CGUtils::ELASTIC_BASE);
		}
		catch(ElasticMissing404Exception $e){
			$message = JSON::decode($e->getMessage());

			// Eat exception if the index we're re-creating does not exist yet
			if ($message['error']['type'] !== 'index_not_found_exception' || $message['error']['index'] !== CGUtils::ELASTIC_BASE['index'])
				throw $e;
		}
		catch (ElasticNoNodesAvailableException $e){
			Response::fail('Re-index failed, ElasticSearch server is down!');
		}
		$params = array_merge(CGUtils::ELASTIC_BASE, [
			'body' => [
				'mappings' => [
					'entry' => [
						'_all' => ['enabled' => false  ],
						'properties' => [
							'label' => [
								'type' => 'text',
								'analyzer' => 'overkill',
							],
							'order' => ['type' => 'integer'],
							'ishuman' => ['type' => 'boolean'],
							'private' => ['type' => 'boolean'],
							'tags' => [
								'type' => 'text',
								'analyzer' => 'overkill',
							],
						],
					],
				],
				'settings' => [
					'analysis' => [
						'analyzer' => [
							'overkill' => [
								'type' => 'custom',
								'tokenizer' => 'overkill',
								'filter' => [
									'lowercase'
								]
							],
						],
						'tokenizer' => [
							'overkill' => [
								'type' => 'edge_ngram',
								'min_gram' => 2,
								'max_gram' => 30,
								'token_chars' => [
									'letter',
									'digit',
								],
							],
						],
					],
				],
			]
		]);
		$elasticClient->indices()->create(array_merge($params));
		/** @var $Appearances Appearance[] */
		$Appearances = DB::$instance->where('id != 0')->where('owner_id IS NULL')->get('appearances');

		$params = ['body' => []];
		foreach ($Appearances as $i => $a){
			$meta = $a->getElasticMeta();
		    $params['body'][] = [
		        'index' => [
		            '_index' => $meta['index'],
		            '_type' => $meta['type'],
		            '_id' => $meta['id'],
		        ]
		    ];

		    $params['body'][] = $a->getElasticBody();

		    if ($i % 100 === 0) {
		        $elasticClient->bulk($params);
		        $params = ['body' => []];
		    }
		}
		if (!empty($params['body'])) {
	        $elasticClient->bulk($params);
		}

		Response::success('Re-index completed');
	}

	public const SPRITE_NAG_USERID = '06af57df-8755-a533-8711-c66f0875209a';

	/**
	 * @param int    $appearance_id
	 * @param string $nag_id        ID of user to nag
	 *
	 * @return Notification[]
	 */
	public static function getSpriteColorIssueNotifications(int $appearance_id, ?string $nag_id = self::SPRITE_NAG_USERID){
		if ($nag_id !== null)
			DB::$instance->where('recipient_id', $nag_id);
		return DB::$instance
			->where('type','sprite-colors')
			->where("data->>'appearance_id'",(string)$appearance_id)
			->where('read_at',null)
			->orderBy('sent_at','DESC')
			->get(Notification::$table_name);
	}

	/**
	 * @param int|Notification[] $appearance_id
	 * @param string             $action        What to set as the notification clearing action
	 * @param string             $nag_id        ID of user to nag
	 */
	public static function clearSpriteColorIssueNotifications($appearance_id, string $action = 'clear', ?string $nag_id = self::SPRITE_NAG_USERID){
		if (\is_int($appearance_id))
			$notifs = self::getSpriteColorIssueNotifications($appearance_id, $nag_id);
		else $notifs = $appearance_id;
		if (empty($notifs))
			return;

		foreach ($notifs as $n)
			$n->safeMarkRead($action);
	}

	/**
	 * @param Appearance[] $Appearances
	 * @param bool $wrap
	 *
	 * @return string
	 */
	public static function getPCGListHTML($Appearances, bool $wrap = WRAP):string {
		if (empty($Appearances))
			$HTML = '<tr><td colspan="4" class="align-center"><em>No appearances to show</em></td></tr>';
		else {
			$HTML = '';
			foreach ($Appearances as $appearance){
				$applink = $appearance->toAnchorWithPreview();
				$owner = $appearance->owner->toAnchor();
				$created = Time::tag($appearance->list);
				if (\count($appearance->cutiemarks) === 0)
					$cms = '<span class="typcn typcn-times"></span>';
				else {
					$cms = '';
					foreach ($appearance->cutiemarks as $cm)
						$cms.= "<a href='{$appearance->toURL()}'>{$cm->getPreviewForAppearancePageListItem()}</a>";
				}
				$spriteUrl = $appearance->getSpriteURL(Appearance::SPRITE_SIZES['SOURCE']);
				$sprite = empty($spriteUrl) ? '<span class="typcn typcn-times"></span>' : "<a href='{$appearance->toURL()}'><img src='{$spriteUrl}' alt='Sprite image'></a>";
				$HTML .= <<<HTML
<tr>
	<td class="pony-link">$applink</td>
	<td>$owner</td>
	<td>$created</td>
	<td class="cutiemarks">$cms</td>
	<td class="sprite">$sprite</td>
</tr>
HTML;
			}

		}
	}
}

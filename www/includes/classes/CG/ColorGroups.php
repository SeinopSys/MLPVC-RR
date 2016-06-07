<?php

	namespace CG;

	class ColorGroups {
		/**
		 * Get color groups
		 *
		 * @param int      $PonyID
		 * @param string   $cols
		 * @param string   $sort_dir
		 * @param int|null $cnt
		 *
		 * @return array
		 */
		static function Get($PonyID, $cols = '*', $sort_dir = 'ASC', $cnt = null){
			global $CGDb;

			self::_order($sort_dir);
			$CGDb->where('ponyid',$PonyID);
			return $cnt === 1 ? $CGDb->getOne('colorgroups',$cols) : $CGDb->get('colorgroups',$cnt,$cols);
		}

		/**
		 * Get the colors belonging to a color group
		 *
		 * @param int $GroupID
		 *
		 * @return array
		 */
		static function GetColors($GroupID){
			global $CGDb;

			return $CGDb->where('groupid', $GroupID)->orderBy('"order"', 'ASC')->get('colors');
		}

		/**
		 * Get the colors belonging to a set of color groups
		 *
		 * @param array $Groups
		 *
		 * @return array
		 */
		static function GetColorsForEach($Groups){
			global $CGDb;

			$GroupIDs = array();
			foreach ($Groups as $g)
				$GroupIDs[] = $g['groupid'];
			if (empty($GroupIDs))
				return null;

			$data = $CGDb->where('groupid IN ('.implode(',',$GroupIDs).')')->orderBy('groupid','ASC')->orderBy('"order"', 'ASC')->get('colors');
			if (empty($data))
				return null;

			$sorted = array();
			foreach ($data as $row)
				$sorted[$row['groupid']][] = $row;
			return $sorted;
		}

		/**
		 * Get HTML for a color group
		 *
		 * @param int|array  $GroupID
		 * @param array|null $AllColors
		 * @param bool       $wrap
		 * @param bool       $colon
		 * @param bool       $colorNames
		 *
		 * @return string
		 */
		static function GetHTML($GroupID, $AllColors = null, $wrap = true, $colon = true, $colorNames = false){
			global $CGDb;

			if (is_array($GroupID)) $Group = $GroupID;
			else $Group = $CGDb->where('groupid',$GroupID)->getOne('colorgroups');

			$label = htmlspecialchars($Group['label']).($colon?': ':'');
			$HTML = $wrap ? "<li id='cg{$Group['groupid']}'>" : '';
			$HTML .=
				"<span class='cat'>$label".
					($colorNames && \Permission::Sufficient('staff')?'<span class="admin"><button class="blue typcn typcn-pencil edit-cg"></button><button class="red typcn typcn-trash delete-cg"></button></span>':'').
				"</span>";
			if (!isset($AllColors))
				$Colors = self::GetColors($Group['groupid']);
			else $Colors = $AllColors[$Group['groupid']] ?? null;
			if (!empty($Colors))
				foreach ($Colors as $i => $c){
					$title = \CoreUtils::AposEncode($c['label']);
					$color = '';
					if (!empty($c['hex'])){
						$color = $c['hex'];
						$title .= "' style='background-color:$color' class='valid-color";
					}

					$append = "<span title='$title'>$color</span>";
					if ($colorNames)
						$append = "<div class='color-line'>$append<span>{$c['label']}</span></div>";
					$HTML .= $append;
				};

			if ($wrap) $HTML .= "</li>";

			return $HTML;
		}

		/**
		 * Order color groups
		 *
		 * @param string $dir
		 */
		 private static function _order($dir = 'ASC'){
			global $CGDb;

			$CGDb
				->orderByLiteral('CASE WHEN "order" IS NULL THEN 1 ELSE 0 END', $dir)
				->orderBy('"order"', $dir)
				->orderBy('groupid', $dir);
		}
	}

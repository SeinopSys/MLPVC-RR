<?php

namespace App;

class View {
	/** @var string */
	public $name, $class, $method;
	public function __construct(string $name){
		[$this->class, $this->method] = self::processName($name);
		$this->name = "$this->class/$this->method";
	}

	public static function processName(string $name){
		$name = strtolower(preg_replace(new RegExp('List$'),'-list',$name));
		if (!preg_match(new RegExp('^(?:\\\\?app\\\\controllers\\\\)?([a-z]+)controller::([a-z-]+)$'), $name, $match))
			throw new \RuntimeException('Could not resolve view based on value '.$name);
		[$class, $method] = array_slice($match, 1, 2);
		return [$class, $method];
	}

	private function _requirePath():string {
		return INCPATH."views/{$this->name}.php";
	}

	public function __toString():string {
		return $this->_requirePath();
	}

	public function getBreadcrumb(array $scope):NavBreadcrumb {
		switch ($this->class){
			case 'about':
				$bc = new NavBreadcrumb('About');
				switch ($this->method){
					case 'browser':
						if (isset($scope['Session'])){
							/** @var $session \App\Models\Session */
							$session = $scope['Session'];
							$bc = (new NavBreadcrumb('Users', '/users'))->setActive(Permission::insufficient('staff'))->setChild(
								(new NavBreadcrumb($session->user->name, $session->user->toURL()))->setChild('Session #'.$session->id)
							);
						}
						else $bc->setChild('Browser Recongition Testing Page');
					break;
					default:
						$bc->setActive();
				}
				return $bc;
			break;
			case 'admin':
				$bc = new NavBreadcrumb('Admin Area', '/admin');
				switch ($this->method){
					case 'discord':
						$bc->setChild('Discord Server Members');
					break;
					case 'ip':
						$bc->setChild(
							(new NavBreadcrumb('Known IPs'))->setChild($scope['ip'])
						);
					break;
					case 'log':
						$bc->setChild('Global Logs');
					break;
					case 'usefullinks':
						$bc->setChild('Manage Useful Links');
					break;
					case 'wsdiag':
						$bc->setChild('WebSocket Server Diagnostics');
					break;
					case 'index':
					default:
						$bc->setActive();
				}
				return $bc;
			break;
			case 'colorguide':
				$eqg = $scope['EQG'] ?? false;
				$bc = new NavBreadcrumb(($eqg?'EQG ':'').'Color Guide', '/cg'.($eqg?'/eqg':''));
				switch ($this->method){
					case 'appearance':
						/** @var $appearance \App\Models\Appearance */
						$appearance = $scope['Appearance'];
						if ($appearance->owner_id !== null)
							$bc = $appearance->owner->getPCGBreadcrumb();
						$bc->end()->setChild($appearance->label);
					break;
					case 'belnding':
						$bc->setChild('Color Blending Calculator');
					break;
					case 'belndingreverse':
						$bc->setChild('Color Blending Reverser');
					break;
					case 'change-list':
						$bc->setChild('List of Major Changes');
					break;
					case 'full-list':
						$bc->setChild('Full List');
					break;
					case 'picker':
						$bc = '';
					break;
					case 'sprite':
						/** @var $appearance \App\Models\Appearance */
						$appearance = $scope['Appearance'];
						if ($appearance->owner_id !== null)
							$bc = $appearance->owner->getPCGBreadcrumb();
						$bc->end()->setChild(
							(new NavBreadcrumb($appearance->label, $appearance->toURL()))->setChild('Sprite Colors')
						);
					break;
					case 'tag-list':
						$bc->setChild('List of Tags');
					break;
					case 'guide':
					default:
						$bc->setActive();
				}
				return $bc;
			break;
			case 'components':
				return new NavBreadcrumb('Components');
			break;
			case 'episode':
				switch ($this->method){
					case 'list':
						return new NavBreadcrumb('Episodes & Movies');
					break;
					case 'view':
						/** @var $ep \App\Models\Episode */
						$ep = $scope['CurrentEpisode'];
						return (new NavBreadcrumb($ep->is_movie ? 'Movies' : 'Episodes', $ep->is_movie ? '/movies' : '/episodes'))->setChild($scope['heading']);
					break;
				}
			break;
			case 'error':
				$bc = new NavBreadcrumb('Error');
				switch ($this->method){
					case 'auth':
						$bc->setChild(new NavBreadcrumb('Auth'));
					break;
					case 'fatal':
						$bc->setChild(new NavBreadcrumb('Fatal'));
					break;
					case 'notfound':
						$bc->setChild(new NavBreadcrumb('Not Found'));
					break;
					default:
						$bc->setActive();
				}
				return $bc;
			break;
			case 'event':
				switch ($this->method){
					case 'list':
						return new NavBreadcrumb('Events');
					break;
					case 'index':
						return (new NavBreadcrumb('Events', '/events'))->setChild($scope['heading']);
					break;
				}
			break;
			case 'user':
				$bc = (new NavBreadcrumb('Users', '/users'))->setActive(Permission::insufficient('staff'));
				if ($this->method !== 'list'){
					/** @var $User \App\Models\User */
					$User = $scope['User'];
					if ($this->method === 'colorguide')
						return $User->getPCGBreadcrumb(true);

					$subbc = new NavBreadcrumb($User->name, $User->toURL());
					switch ($this->method){
						case 'contrib':
							$subbc->setChild(
								(new NavBreadcrumb('Contributions'))->setChild($scope['contribName'])
							);
						break;
						case 'profile':
							$subbc->setActive();
						break;
					}
					$bc->setChild($subbc);
				}
				else $bc->setActive();
				return $bc;
			break;
		}
	}
}

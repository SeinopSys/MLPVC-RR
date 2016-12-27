<?php

// TODO Add all missing routes

namespace App;

$router = new \AltoRouter();
$router->addMatchTypes([
	'un' => USERNAME_PATTERN,
	'au' => '[A-Za-z_]+',
	'ad' => '[A-Za-z\-]+',
	'adi' => '[A-Za-z\d\-]+',
	'epid' => EPISODE_ID_PATTERN,
	'rr' => '(req|res)',
	'rrl' => '(request|reservation)',
	'rrsl' => '(request|reservation)s?',
	'cgimg' => '[spd]',
	'cgext' => '(png|svg|json|gpl)',
	'eqg' => 'eqg',
	'gsd' => '([gs]et|del)',
	'make' => 'make',
]);

// Pages
$router->map('GET',  '/',                               'EpisodeController#index');
$router->map('GET',  '/footer-git',                     'FooterController#git');
$router->map('GET',  '/about',                          'AboutController#index');
$router->map('GET',  '/admin',                          'AdminController#index');
$router->map('GET',  '/admin/logs/[i]?',                'AdminController#logs');
$router->map('GET',  '/blending',                       'BlendingController#index');
$router->map('GET',  '/browser/[i:session]?',           'BrowserController#index');
$router->map('GET',  '/components',                     'ComponentsController#index');
$router->map('GET',      '/cg/[eqg:eqg]?/[i]?',                             'ColorGuideController#guide');
$router->map('GET',      '/cg/[eqg:eqg]?/full',                             'ColorGuideController#fullList');
$router->map('GET',      '/cg/[eqg:eqg]?/tags/[i]?',                        'ColorGuideController#tagList');
$router->map('GET',      '/cg/[eqg:eqg]?/changes/[i]?',                     'ColorGuideController#changeList');
$router->map('GET',      '/cg/[eqg:eqg]?/v/[i:id][adi]?',                   'ColorGuideController#appearancePage');
$router->map('GET',      '/cg/[eqg:eqg]?/v/[i:id][cgimg:type].[cgext:ext]', 'ColorGuideController#appearanceAsFile');
$router->map('GET',      '/cg/[eqg:eqg]?/sprite(-colors)?/[i:id][adi]?',    'ColorGuideController#spriteColors');
$router->map('GET|POST', '/cg/get-tags',                                    'ColorGuideController#getTags');
$router->map('GET',  '/errorlog',                       'ErrorLogController#index');
$router->map('GET',  '/da-auth',                        'AuthController#auth');
$router->map('GET',  '/episode/[epid:id]',              'EpisodeController#page');
$router->map('GET',  '/episodes/[i]?',                  'EpisodesController#index');
$router->map('GET',  '/eqg/[i:id]',                     'EQGController#redirectInt');
$router->map('GET',  '/eqg/[adi:id]',                   'EQGController#redirectStr');
$router->map('GET',  '/movie/[i:id][adi]?',             'MovieController#pageID');
$router->map('GET',  '/movie/[adi:title]',              'MovieController#pageTitle');
$router->map('GET',  '/logs/[i]',                       'AdminController#logs');
$router->map('GET',  '/muffin-rating',                  'MuffinRatingController#image');
$router->map('GEP',  '/poly',                           'PolyController#index');
$router->map('GET',  '/s/[rr:thing]/[i:id]',            'PostController#share');
$router->map('GET',  '/users',                          'UsersController#list');
$router->map('GET',  '/u/[un:name]?',                   'UserController#profile');
$router->map('GET',  '/user/[un:name]?',                'UserController#profile');

// "API" Endpoints
$router->map('POST', '/about/stats',                         'AboutController#stats');
$router->map('POST', '/admin/logs/details/[i:id]',           'AdminController#logDetail');
$router->map('POST', '/admin/usefullinks',                   'AdminController#usefulLinks');
$router->map('POST', '/admin/usefullinks/reorder',           'AdminController#reorderUsefulLinks');
$router->map('POST', '/admin/mass-approve',                  'AdminController#massApprove');
$router->map('POST', '/cg/full/reorder',                     'ColorGuideController#reorderFullList');
$router->map('POST', '/cg/export',                           'ColorGuideController#export');
$router->map('POST', '/cg/appearance/[ad:action]/[i:id]',    'ColorGuideController#appearanceAction');
$router->map('POST', '/cg/appearance/[make:action]',         'ColorGuideController#appearanceAction');
$router->map('POST', '/cg/tag/[ad:action]/[i:id]',           'ColorGuideController#tagAction');
$router->map('POST', '/cg/tag/[make:action]',                'ColorGuideController#tagAction');
$router->map('POST', '/cg/tags/recount-uses',                'ColorGuideController#recountTagUses');
$router->map('POST', '/cg/colorgroup/[gsd:action]/[i:id]',   'ColorGuideController#colorGroupAction');
$router->map('POST', '/cg/colorgroup/[make:action]',         'ColorGuideController#colorGroupAction');
//                    /cg/...
$router->map('POST', '/da-auth/signout',                     'AuthController#signout');
$router->map('POST', '/episode/postlist/[epid:id]',          'EpisodeController#postList');
$router->map('POST', '/episode/get/[epid:id]',               'EpisodeController#get');
$router->map('POST', '/episode/delete/[epid:id]',            'EpisodeController#delete');
$router->map('POST', '/episode/set/[epid:id]',               'EpisodeController#set');
$router->map('POST', '/episode/add',                         'EpisodeController#add');
$router->map('POST', '/episode/vote/[epid:id]',              'EpisodeController#vote');
$router->map('POST', '/episode/video-embeds/[epid:id]',      'EpisodeController#getVideoEmbeds');
$router->map('POST', '/episode/video-data/[epid:id]',        'EpisodeController#videoData');
$router->map('POST', '/episode/guide-relations/[epid:id]',   'EpisodeController#guideRelations');
$router->map('POST', '/episode/broken-videos/[epid:id]',     'EpisodeController#brokenVideos');
$router->map('POST', '/notifications/get',                   'NotificationsController#get');
$router->map('POST', '/notifications/mark-read/[i:id]',      'NotificationsController#markRead');
$router->map('POST', '/ping',                                'PingController#ping');
$router->map('POST', '/post/reload/[rrl:thing]/[i:id]',      'PostController#reload');
$router->map('POST', '/post/[a:action]/[rrsl:thing]/[i:id]', 'PostController#action');
$router->map('POST', '/post/add',                            'PostController#add');
$router->map('POST', '/post/set-image/[rrl:thing]/[i:id]',   'PostController#setImage');
$router->map('POST', '/post/check-image',                    'PostController#checkImage');
$router->map('POST', '/post/fix-stash/[rrl:thing]/[i:id]',   'PostController#fixStash');
$router->map('POST', '/post/add-reservation',                'PostController#addReservation');
$router->map('POST', '/post/delete-request/[i:id]',          'PostController#deleteRequest');
$router->map('POST', '/post/transfer/[rrl:thing]/[i:id]',    'PostController#queryTransfer');
$router->map('POST', '/preference/set/[au:key]',             'PreferenceController#set');
$router->map('POST', '/preference/get/[au:key]',             'PreferenceController#get');
$router->map('POST', '/cg/reindex',                          'ColorGuideController#reindex');
$router->map('POST', '/setting/set/[au:key]',                'SettingController#set');
$router->map('POST', '/setting/get/[au:key]',                'SettingController#get');
$router->map('POST', '/user/suggestion',                     'UserController#suggestion');
$router->map('POST', '/user/discord-verify',                 'UserController#discordVerify');
$router->map('POST', '/user/sessiondel/[i:id]',              'UserController#sessionDel');
$router->map('POST', '/user/setgroup/[un:name]',             'UserController#setGroup');
$router->map('POST', '/user/banish/[un:name]',               'UserController#banish');
$router->map('POST', '/user/un-banish/[un:name]',            'UserController#unbanish');

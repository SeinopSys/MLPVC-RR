<?php

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
	'cgimg' => '[spfc]',
	'cgext' => '(png|svg|json|gpl)',
	'guide' => '(eqg|pony)',
	'favme' => 'd[a-z\d]{6}',
	'gsd' => '([gs]et|del)',
	'cg' => '(c(olou?r)?g(uide)?)',
	'user' => 'u(ser)?',
	'v' => '(v|appearance)',
	'uuid' => '([0-9a-fA-F]{32}|[0-9a-fA-F-]{36})',
]);

// Pages
# AboutController
$router->map('GET', '/about',                         'AboutController#index');
$router->map('GET', '/about/browser/[uuid:session]?', 'AboutController#browser');
$router->map('GET', '/browser/[uuid:session]?',       'AboutController#browser');
$router->map('GET', '/about/privacy',                 'AboutController#privacy');
# AdminController
$router->map('GET', '/admin',                      'AdminController#index');
$router->map('GET', '/logs/[i]?',                  'AdminController#log');
$router->map('GET', '/logs/[i]',                   'AdminController#log');
$router->map('GET', '/admin/logs/[i]?',            'AdminController#log');
$router->map('GET', '/admin/discord',              'AdminController#discord');
$router->map('GET', '/admin/usefullinks',          'AdminController#usefulLinks');
$router->map('GET', '/admin/wsdiag',               'AdminController#wsdiag');
$router->map('GET', '/admin/pcg-appearances/[i]?', 'AdminController#pcgAppearances');
$router->map('GET', '/admin/notices',              'AdminController#notices');
# ColorGuideController
$router->map('GET', '/blending',                           'ColorGuideController#blending');
$router->map('GET', '/[cg]/blending',                      'ColorGuideController#blending');
$router->map('GET', '/[cg]/blending-reverse',              'ColorGuideController#blendingReverse');
$router->map('GET', '/[cg]/picker',                        'ColorGuideController#picker');
$router->map('GET', '/[cg]/picker/frame',                  'ColorGuideController#pickerFrame');
$router->map('GET', '/[cg]/[guide:guide]?/[i]?',           'ColorGuideController#guide');
$router->map('GET', '/[cg]/[guide:guide]?/full',           'ColorGuideController#fullList');
$router->map('GET', '/[cg]/[guide:guide]?/changes/[i]?',   'ColorGuideController#changeList');
$router->map('GET', '/[cg]/[guide:guide]?/[v]',            'ColorGuideController#guide');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/[v]', 'ColorGuideController#guide');
# AppearanceController
$router->map('GET', '/[cg]/[guide:guide]?/[v]/[i:id]',                                        'AppearanceController#view');
$router->map('GET', '/[cg]/[guide:guide]?/[v]/[i:id]-[adi]',                                  'AppearanceController#view');
$router->map('GET', '/[cg]/[guide:guide]?/[v]/[adi]-[i:id]',                                  'AppearanceController#view');
$router->map('GET', '/[cg]/[guide:guide]?/[v]/[i:id][cgimg:type]?.[cgext:ext]',               'AppearanceController#asFile');
$router->map('GET', '/[cg]/[guide:guide]?/sprite(-colors)?/[i:id][adi]?',                     'AppearanceController#sprite');
$router->map('GET', '/[cg]/[guide:guide]?/tag-changes/[i:id][adi]?',                          'AppearanceController#tagChanges');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/[v]/[i:id]',                             'AppearanceController#view');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/[v]/[i:id]-[adi]',                       'AppearanceController#view');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/[v]/[adi]-[i:id]',                       'AppearanceController#view');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/[v]/[i:id][cgimg:type]?.[cgext:ext]',    'AppearanceController#asFile');
$router->map('GET', '/@[un:name]/[cg]/[guide:guide]?/sprite(-colors)?/[i:id][adi]?',          'AppearanceController#sprite');
# ComponentsController
$router->map('GET', '/components', 'ComponentsController#index');
# TagController
$router->map('GET', '/[cg]/[guide:guide]?/tags/[i]?', 'TagController#list');
# CutiemarkController
$router->map('GET', '/[cg]/cutiemark/[i:id].svg',            'CutiemarkController#view');
$router->map('GET', '/[cg]/cutiemark/download/[i:id][adi]?', 'CutiemarkController#download');
# AuthController
$router->map('GET', '/da-auth',       'AuthController#end');
$router->map('GET', '/da-auth/begin', 'AuthController#begin');
$router->map('GET', '/da-auth/end',   'AuthController#end');
# DiscordAuthController
$router->map('GET', '/discord-connect/begin', 'DiscordAuthController#begin');
$router->map('GET', '/discord-connect/end',   'DiscordAuthController#end');
# EpisodeController
$router->map('GET', '/episode/[epid:id]', 'EpisodeController#view');
$router->map('GET', '/episode/latest',    'EpisodeController#latest');
# ShowController
$router->map('GET', '/episodes/[i]?', 'ShowController#index');
$router->map('GET', '/movies/[i]?',   'ShowController#index');
$router->map('GET', '/show',          'ShowController#index');
# EQGController
$router->map('GET', '/eqg/[i:id]',   'EQGController#redirectInt');
$router->map('GET', '/eqg/[adi:id]', 'EQGController#redirectStr');
# EventController
$router->map('GET', '/events/[i]?',                 'EventController#list');
$router->map('GET', '/event/[i:id][adi]?',          'EventController#index');
$router->map('GET', '/event/entry/lazyload/[i:id]', 'EventController#lazyloadEntry');
# MovieController
$router->map('GET', '/movie/[i:id][adi]?', 'MovieController#view');
# MuffinRatingController
$router->map('GET', '/muffin-rating', 'MuffinRatingController#image');
# PostController
$router->map('GET', '/s/[rr:thing]/[i:id]', 'PostController#share');
$router->map('GET', '/post/lazyload/[rrl:thing]/[i:id]', 'PostController#lazyload');
# UserController
$router->map('GET', '/',                                    'UserController#homepage');
$router->map('GET', '/users',                               'UserController#list');
$router->map('GET', '/@[un:name]',                          'UserController#profile');
$router->map('GET', '/u/[un:name]?',                        'UserController#profile');
$router->map('GET', '/u/[uuid:uuid]',                       'UserController#profileByUuid');
$router->map('GET', '/@[un:name]/contrib/[ad:type]/[i]?',   'UserController#contrib');
$router->map('GET', '/user/contrib/lazyload/[favme:favme]', 'UserController#contribLazyload');
# PersonalGuideController
$router->map('GET', '/@[un:name]/[cg]/[i]?',               'PersonalGuideController#list');
$router->map('GET', '/@[un:name]/[cg]/slot-history/[i]?',  'PersonalGuideController#pointHistory');
$router->map('GET', '/@[un:name]/[cg]/point-history/[i]?', 'PersonalGuideController#pointHistory');
# ManifestController
$router->map('GET', '/manifest', 'ManifestController#json');

// Proper REST API endpoints (sort of)
// Allowing all request methods lets us reply with HTTP 405 to unsupported methods at the controller level
\define('API_PATH', '/api/private');
$api_endpoint = function($path, $controller) use ($router){
	$router->map('POST|GET|PUT|DELETE', API_PATH.$path, $controller);
};
$api_endpoint('/about/upcoming',                     'AboutController#upcoming');
$api_endpoint('/admin/logs/details/[i:id]',          'AdminController#logDetail');
$api_endpoint('/admin/usefullinks/[i:id]?',          'AdminController#usefulLinksApi');
$api_endpoint('/admin/usefullinks/reorder',          'AdminController#reorderUsefulLinks');
$api_endpoint('/admin/wsdiag/hello',                 'AdminController#wshello');
$api_endpoint('/admin/mass-approve',                 'AdminController#massApprove');
$api_endpoint('/admin/notices/[i:id]?',              'AdminController#noticesApi');
$api_endpoint('/cg/appearances/list',                'AppearanceController#listApi');
$api_endpoint('/cg/appearance/[i:id]?',              'AppearanceController#api');
$api_endpoint('/cg/appearance/[i:id]/colorgroups',   'AppearanceController#colorGroupsApi');
$api_endpoint('/cg/appearance/[i:id]/sprite',        'AppearanceController#spriteApi');
$api_endpoint('/cg/appearance/[i:id]/relations',     'AppearanceController#relationsApi');
$api_endpoint('/cg/appearance/[i:id]/cutiemarks',    'AppearanceController#cutiemarkApi');
$api_endpoint('/cg/appearance/[i:id]/tagged',        'AppearanceController#taggedApi');
$api_endpoint('/cg/appearance/[i:id]/template',      'AppearanceController#applyTemplate');
$api_endpoint('/cg/appearance/[i:id]/sanitize-svg',  'AppearanceController#sanitizeSvg');
$api_endpoint('/cg/appearance/[i:id]/selective',     'AppearanceController#selectiveClear');
$api_endpoint('/cg/appearance/[i:id]/link-targets',  'AppearanceController#linkTargets');
$api_endpoint('/cg/sprite-color-checkup',            'ColorGuideController#spriteColorCheckup');
$api_endpoint('/cg/full/reorder',                    'ColorGuideController#reorderFullList');
$api_endpoint('/cg/export',                          'ColorGuideController#export');
$api_endpoint('/cg/reindex',                         'ColorGuideController#reindex');
$api_endpoint('/cg/tags',                            'TagController#autocomplete');
$api_endpoint('/cg/tags/recount-uses',               'TagController#recountUses');
$api_endpoint('/cg/tag/[i:id]?',                     'TagController#api');
$api_endpoint('/cg/tag/[i:id]/synonym',              'TagController#synonymApi');
$api_endpoint('/cg/colorgroup/[i:id]?',              'ColorGroupController#api');
$api_endpoint('/@[un:name]/cg/slot-check',           'PersonalGuideController#checkAvailSlots');
$api_endpoint('/@[un:name]/cg/point-history/recalc', 'PersonalGuideController#pointRecalc');
$api_endpoint('/da-auth/status',                     'AuthController#sessionStatus');
$api_endpoint('/da-auth/sign-out',                   'AuthController#signOut');
$api_endpoint('/episode/[epid:id]?',                 'EpisodeController#api');
$api_endpoint('/episode/[epid:id]/posts',            'EpisodeController#postList');
$api_endpoint('/episode/[epid:id]/vote',             'EpisodeController#voteApi');
$api_endpoint('/episode/[epid:id]/video-embeds',     'EpisodeController#videoEmbeds');
$api_endpoint('/episode/[epid:id]/video-data',       'EpisodeController#videoDataApi');
$api_endpoint('/episode/[epid:id]/guide-relations',  'EpisodeController#guideRelationsApi');
$api_endpoint('/episode/[epid:id]/broken-videos',    'EpisodeController#brokenVideos');
$api_endpoint('/episode/next',                       'EpisodeController#next');
$api_endpoint('/episode/prefill',                    'EpisodeController#prefill');

// "API" Endpoints
$router->map('POST', '/event/get/[i:id]',                    'EventController#get');
$router->map('POST', '/event/del/[i:id]',                    'EventController#delete');
$router->map('POST', '/event/set/[i:id]',                    'EventController#set');
$router->map('POST', '/event/add',                           'EventController#add');
$router->map('POST', '/event/finalize/[i:id]',               'EventController#finalize');
$router->map('POST', '/event/check-entries/[i:id]',          'EventController#checkEntries');
$router->map('POST', '/event/entry/add/[i:id]',              'EventController#addEntry');
$router->map('POST', '/event/entry/get/[i:entryid]',         'EventController#getEntry');
$router->map('POST', '/event/entry/set/[i:entryid]',         'EventController#setEntry');
$router->map('POST', '/event/entry/del/[i:entryid]',         'EventController#delEntry');
$router->map('POST', '/event/entry/vote/[i:entryid]',        'EventController#voteEntry');
$router->map('POST', '/event/entry/unvote/[i:entryid]',      'EventController#unvoteEntry');
$router->map('POST', '/event/entry/getvote/[i:entryid]',     'EventController#getvoteEntry');
$router->map('POST', '/notifications/get',                   'NotificationsController#get');
$router->map('POST', '/notifications/mark-read/[i:id]',      'NotificationsController#markRead');
$router->map('POST', '/post/reload/[rrl:thing]/[i:id]',      'PostController#reload');
$router->map('POST', '/post/transfer/[rrl:thing]/[i:id]',    'PostController#queryTransfer');
$router->map('POST', '/post/[a:action]/[rrsl:thing]/[i:id]', 'PostController#action');
$router->map('POST', '/post/add',                            'PostController#add');
$router->map('POST', '/post/set-image/[rrl:thing]/[i:id]',   'PostController#setImage');
$router->map('POST', '/post/check-image',                    'PostController#checkImage');
$router->map('POST', '/post/fix-stash/[rrl:thing]/[i:id]',   'PostController#fixStash');
$router->map('POST', '/post/add-reservation',                'PostController#addReservation');
$router->map('POST', '/post/delete-request/[i:id]',          'PostController#deleteRequest');
$router->map('POST', '/post/locate/[rrl:thing]/[i:id]',      'PostController#locate');
$router->map('POST', '/@[un:name]?/preference/set/[au:key]',  'PreferenceController#set');
$router->map('POST', '/@[un:name]?/preference/get/[au:key]',  'PreferenceController#get');
$router->map('POST', '/setting/set/[au:key]',                'SettingController#set');
$router->map('POST', '/setting/get/[au:key]',                'SettingController#get');
$router->map('POST', '/user/suggestion',                     'UserController#suggestion');
$router->map('POST', '/user/sessiondel/[uuid:id]',           'UserController#sessionDel');
$router->map('POST', '/user/setrole/[un:name]',              'UserController#setRole');
$router->map('POST', '/user/setdevrolemask',                 'UserController#setDevRoleMask');
$router->map('POST', '/user/awaiting-approval/[un:name]',    'UserController#awaitingApproval');
$router->map('POST', '/user/avatar-wrap/[un:name]',          'UserController#avatarWrap');
$router->map('POST', '/user/verify-giftable-slots',          'PersonalGuideController#verifyGiftableSlots');
$router->map('POST', '/user/gift-pcg-slots/[un:name]',       'PersonalGuideController#giftSlots');
$router->map('POST', '/user/pending-gifts/[un:name]',        'PersonalGuideController#getPendingSlotGifts');
$router->map('POST', '/user/refund-gifts',                   'PersonalGuideController#refundSlotGifts');
$router->map('POST', '/user/give-pcg-points/[un:name]',      'PersonalGuideController#givePoints');
$router->map('POST', '/user/get-deductable-points/[un:name]', 'PersonalGuideController#getDeductablePoints');
$router->map('POST', '/discord-connect/sync/[un:name]',      'DiscordAuthController#sync');
$router->map('POST', '/discord-connect/unlink/[un:name]',    'DiscordAuthController#unlink');
$router->map('POST', '/discord-connect/bot-update/[i:id]',   'DiscordAuthController#botUpdate');

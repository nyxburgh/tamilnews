<?php
return [

    /* ── STAFF LOGIN (separate from admin) ── */
    ['GET',  '/login',  'UserAuthController@loginForm'],
    ['POST', '/login',  'UserAuthController@login'],
    ['GET',  '/logout', 'UserAuthController@logout'],

    /* ── TAG PAGES ── */
    ['GET',  '/tag/{slug}',    'frontend\TagController@show'],

    /* ── AUTHOR PAGES ── */
    ['GET',  '/author/{slug}', 'frontend\AuthorController@show'],

    /* ── PENDING EDITS ── */
    ['GET',  '/admin/articles/pending-edits',       'admin\ArticleController@pendingEdits'],
    ['POST', '/admin/articles/approve-edit/{id}',   'admin\UserController@approveEdit'],
    ['POST', '/admin/articles/reject-edit/{id}',    'admin\UserController@rejectEdit'],


    /* ── NEWSPAPER ARCHIVE (ADMIN) ── */
    ['GET',  '/admin/newspaper',              'admin\NewspaperController@index'],
    ['POST', '/admin/newspaper/upload',       'admin\NewspaperController@upload'],
    ['POST', '/admin/newspaper/toggle/{id}',  'admin\NewspaperController@toggle'],
    ['POST', '/admin/newspaper/delete/{id}',  'admin\NewspaperController@delete'],

    /* ── NEWSPAPER ARCHIVE (FRONTEND) ── */
    ['GET',  '/newspaper',                    'frontend\NewspaperController@index'],
    ['GET',  '/newspaper/read/{date}',        'frontend\NewspaperController@showPaper'],
    ['GET',  '/newspaper/download/{id}',      'frontend\NewspaperController@download'],

    /* ── AUTH ── */
    ['GET',  '/admin/login',  'AuthController@loginForm'],
    ['POST', '/admin/login',  'AuthController@login'],
    ['GET',  '/admin/logout', 'AuthController@logout'],
    /* ── DASHBOARD ── */
    ['GET', '/admin',           'admin\DashboardController@index'],
    ['GET', '/admin/dashboard', 'admin\DashboardController@index'],
    /* ── ARTICLES ── */
    ['GET',  '/admin/articles',              'admin\ArticleController@index'],
    ['GET',  '/admin/articles/create',       'admin\ArticleController@create'],
    ['POST', '/admin/articles/create',       'admin\ArticleController@store'],
    ['GET',  '/admin/articles/edit/{id}',    'admin\ArticleController@edit'],
    ['POST', '/admin/articles/edit/{id}',    'admin\ArticleController@update'],
    ['POST', '/admin/articles/delete/{id}',  'admin\ArticleController@delete'],
    ['POST', '/admin/articles/bulk',         'admin\ArticleController@bulk'],
    ['POST', '/admin/articles/toggle-breaking/{id}', 'admin\ArticleController@toggleBreaking'],
    /* ── CATEGORIES ── */
    ['GET',  '/admin/categories',             'admin\CategoryController@index'],
    ['POST', '/admin/categories/create',      'admin\CategoryController@store'],
    ['POST', '/admin/categories/edit/{id}',   'admin\CategoryController@update'],
    ['POST', '/admin/categories/delete/{id}', 'admin\CategoryController@delete'],
    ['POST', '/admin/categories/sort',        'admin\CategoryController@sort'],
    /* ── TAGS ── */
    ['GET',  '/admin/tags',             'admin\TagController@index'],
    ['POST', '/admin/tags/create',      'admin\TagController@store'],
    ['POST', '/admin/tags/edit/{id}',   'admin\TagController@update'],
    ['POST', '/admin/tags/delete/{id}', 'admin\TagController@delete'],
    ['GET',  '/admin/tags/suggest',     'admin\TagController@suggest'],
    /* ── LOCATIONS ── */
    ['GET',  '/admin/locations',                    'admin\LocationController@index'],
    ['POST', '/admin/locations/states/create',      'admin\LocationController@storeState'],
    ['POST', '/admin/locations/districts/create',   'admin\LocationController@storeDistrict'],
    ['POST', '/admin/locations/cities/create',      'admin\LocationController@storeCity'],
    ['POST', '/admin/locations/delete/{type}/{id}', 'admin\LocationController@delete'],
    /* ── MEDIA ── */
    ['GET',  '/admin/media',             'admin\MediaController@index'],
    ['POST', '/admin/media/upload',      'admin\MediaController@upload'],
    ['POST', '/admin/media/delete/{id}', 'admin\MediaController@delete'],
    ['GET',  '/admin/media/modal',       'admin\MediaController@modal'],
    /* ── USERS ── */
    ['GET',  '/admin/users',             'admin\UserController@index'],
    ['GET',  '/admin/users/create',      'admin\UserController@create'],
    ['POST', '/admin/users/create',      'admin\UserController@store'],
    ['GET',  '/admin/users/edit/{id}',   'admin\UserController@edit'],
    ['POST', '/admin/users/edit/{id}',   'admin\UserController@update'],
    ['POST', '/admin/users/delete/{id}', 'admin\UserController@delete'],
    /* ── SETTINGS ── */
    ['GET',  '/admin/settings',         'admin\SettingController@index'],
    ['POST', '/admin/settings',         'admin\SettingController@update'],
    ['GET',  '/admin/settings/{group}', 'admin\SettingController@group'],
    ['POST', '/admin/settings/{group}', 'admin\SettingController@updateGroup'],
    /* ── YOUTUBE ── */
    ['GET',  '/admin/youtube',                      'admin\YoutubeController@index'],
    ['POST', '/admin/youtube/channels/create',      'admin\YoutubeController@storeChannel'],
    ['POST', '/admin/youtube/channels/edit/{id}',   'admin\YoutubeController@updateChannel'],
    ['POST', '/admin/youtube/channels/delete/{id}', 'admin\YoutubeController@deleteChannel'],
    ['POST', '/admin/youtube/keywords/create',      'admin\YoutubeController@storeKeyword'],
    ['POST', '/admin/youtube/keywords/delete/{id}', 'admin\YoutubeController@deleteKeyword'],
    ['GET',  '/admin/youtube/imports',              'admin\YoutubeController@imports'],
    ['POST', '/admin/youtube/imports/publish/{id}', 'admin\YoutubeController@publishImport'],
    /* ── RSS ── */
    ['GET',  '/admin/rss',                      'admin\RssController@index'],
    ['POST', '/admin/rss/create',               'admin\RssController@store'],
    ['POST', '/admin/rss/edit/{id}',            'admin\RssController@update'],
    ['POST', '/admin/rss/delete/{id}',          'admin\RssController@delete'],
    ['GET',  '/admin/rss/imports',              'admin\RssController@imports'],
    ['POST', '/admin/rss/imports/publish/{id}', 'admin\RssController@publish'],
    ['POST', '/admin/rss/imports/skip/{id}',    'admin\RssController@skip'],
    /* ── ADS ── */
    ['GET',  '/admin/ads',           'admin\AdController@index'],
    ['POST', '/admin/ads/edit/{id}', 'admin\AdController@update'],
    /* ── PUSH ── */
    ['GET',  '/admin/push',         'admin\PushController@index'],
    ['POST', '/admin/push/send',    'admin\PushController@send'],
    ['GET',  '/admin/push/history', 'admin\PushController@history'],
    /* ── ANALYTICS ── */
    ['GET', '/admin/analytics',          'admin\AnalyticsController@index'],
    ['GET', '/admin/analytics/articles', 'admin\AnalyticsController@articles'],
    /* ── CONTRIBUTORS ── */
    ['GET',  '/admin/contributors',                 'admin\ContributorController@index'],
    ['POST', '/admin/contributors/create',          'admin\ContributorController@store'],
    ['GET',  '/admin/contributors/show/{id}',       'admin\ContributorController@show'],
    ['POST', '/admin/contributors/approve/{id}',    'admin\ContributorController@approve'],
    ['POST', '/admin/contributors/reject/{id}',     'admin\ContributorController@reject'],
    ['POST', '/admin/contributors/categories/{id}', 'admin\ContributorController@updateCategories'],
    ['POST', '/admin/contributors/delete/{id}',     'admin\ContributorController@delete'],


    /* ── LIVE BLOG (ADMIN) ── */
    ['GET',  '/admin/live-blog',                    'admin\LiveBlogController@index'],
    ['GET',  '/admin/live-blog/create',             'admin\LiveBlogController@create'],
    ['POST', '/admin/live-blog/create',             'admin\LiveBlogController@store'],
    ['GET',  '/admin/live-blog/manage/{id}',        'admin\LiveBlogController@manage'],
    ['POST', '/admin/live-blog/post-entry/{id}',    'admin\LiveBlogController@postEntry'],
    ['POST', '/admin/live-blog/delete-entry/{id}',  'admin\LiveBlogController@deleteEntry'],
    ['POST', '/admin/live-blog/end/{id}',           'admin\LiveBlogController@end'],
    ['POST', '/admin/live-blog/reactivate/{id}',    'admin\LiveBlogController@reactivate'],
    ['POST', '/admin/live-blog/delete/{id}',        'admin\LiveBlogController@delete'],

    /* ── PREMIUM (ADMIN) ── */
    ['GET',  '/admin/premium',                      'admin\PremiumController@index'],
    ['POST', '/admin/premium/toggle/{id}',          'admin\PremiumController@toggle'],
    ['GET',  '/admin/premium/plans',                'admin\PremiumController@plans'],
    ['POST', '/admin/premium/plans/create',         'admin\PremiumController@storePlan'],
    ['POST', '/admin/premium/plans/update/{id}',    'admin\PremiumController@updatePlan'],
    ['GET',  '/admin/premium/subscribers',          'admin\PremiumController@subscribers'],

    /* ── LIVE BLOG (FRONTEND) ── */
    ['GET',  '/live/{slug}',                        'frontend\LiveBlogController@show'],
    ['GET',  '/api/live/{id}/poll',                 'frontend\LiveBlogController@poll'],

    /* ── SPECIAL CATEGORIES (ADMIN) ── */
    ['GET',  '/admin/special-categories',                      'admin\SpecialCategoryController@index'],
    ['POST', '/admin/special-categories/create',               'admin\SpecialCategoryController@store'],
    ['GET',  '/admin/special-categories/edit/{id}',            'admin\SpecialCategoryController@edit'],
    ['POST', '/admin/special-categories/edit/{id}',            'admin\SpecialCategoryController@update'],
    ['POST', '/admin/special-categories/delete/{id}',          'admin\SpecialCategoryController@delete'],
    ['POST', '/admin/special-categories/add-article/{id}',     'admin\SpecialCategoryController@addArticle'],
    ['POST', '/admin/special-categories/remove-article/{id}',  'admin\SpecialCategoryController@removeArticle'],

    /* ── EDITOR/REPORTER PORTAL ── */
    ['GET',  '/portal/dashboard',        'frontend\EditorPortalController@dashboard'],
    ['GET',  '/portal/articles',         'frontend\EditorPortalController@myArticles'],
    ['GET',  '/portal/profile',          'frontend\EditorPortalController@profile'],
    ['POST', '/portal/profile/update',   'frontend\EditorPortalController@updateProfile'],

    /* ── SPECIAL CATEGORY FRONTEND ── */
    ['GET',  '/special/{slug}',          'frontend\SpecialPageController@show'],

    /* ── PRINT EDITION (Stage 1 — Article Selection) ── */
    ['GET',  '/admin/print',                      'admin\PrintEditionController@index'],
    ['GET',  '/admin/print/create',               'admin\PrintEditionController@create'],
    ['POST', '/admin/print/store',                'admin\PrintEditionController@store'],
    ['GET',  '/admin/print/select/{id}',          'admin\PrintEditionController@select'],
    ['POST', '/admin/print/toggle-article/{id}',  'admin\PrintEditionController@toggleArticle'],
    ['POST', '/admin/print/sort/{id}',            'admin\PrintEditionController@updateSort'],
    ['GET',  '/admin/print/manage/{id}',          'admin\PrintEditionController@manage'],
    ['POST', '/admin/print/status/{id}',          'admin\PrintEditionController@updateStatus'],
    ['POST', '/admin/print/delete/{id}',          'admin\PrintEditionController@delete'],

    /* ── USER PERMISSIONS ── */
    ['POST', '/admin/users/perm-remove/{id}', 'admin\UserController@removePerm'],
    ['GET',  '/admin/media/folder-move',      'admin\MediaController@moveFolder'],
    ['POST', '/admin/media/folder-move',      'admin\MediaController@moveFolder'],

    /* ── USER BLOCK / BADGE ── */
    ['POST', '/admin/users/block/{id}',          'admin\UserController@block'],
    ['POST', '/admin/users/unblock/{id}',        'admin\UserController@unblock'],
    ['POST', '/admin/users/badge/assign/{id}',   'admin\UserController@assignBadge'],
    ['POST', '/admin/users/badge/remove/{id}',   'admin\UserController@removeBadge'],
    ['POST', '/admin/articles/approve-edit/{id}','admin\UserController@approveEdit'],
    ['POST', '/admin/articles/reject-edit/{id}', 'admin\UserController@rejectEdit'],


    /* ── ARTICLE APPROVAL ACTIONS ── */
    ['POST', '/admin/articles/approve/{id}',  'admin\ArticleController@approve'],
    ['POST', '/admin/articles/reject/{id}',   'admin\ArticleController@reject'],

    /* ── PORTAL NOTIFICATIONS ── */
    ['GET',  '/portal/notifications',          'frontend\EditorPortalController@notifications'],
    ['POST', '/portal/notifications/read',     'frontend\EditorPortalController@markRead'],

    /* ── PORTAL (unified: editor+reporter+contributor) ── */
    ['GET',  '/portal/dashboard',      'frontend\EditorPortalController@dashboard'],
    ['GET',  '/portal/articles',       'frontend\EditorPortalController@myArticles'],
    ['GET',  '/portal/profile',        'frontend\EditorPortalController@profile'],
    ['POST', '/portal/profile/update', 'frontend\EditorPortalController@updateProfile'],
    ['POST', '/portal/articles/edit/{id}', 'frontend\EditorPortalController@submitEdit'],

    /* ── LIVE BLOG API (external post) ── */
    ['POST', '/api/live/{id}/post',    'admin\LiveBlogController@apiPost'],

    /* ── CONTRIBUTOR PORTAL ── */
    ['GET',  '/contribute/login',           'ContributorAuthController@loginPage'],
    ['GET',  '/contribute/auth/google',     'ContributorAuthController@googleRedirect'],
    ['GET',  '/contribute/auth/callback',   'ContributorAuthController@callback'],
    ['GET',  '/contribute/logout',          'ContributorAuthController@logout'],
    ['GET',  '/contribute/dashboard',       'contribute\DashboardController@index'],
    ['GET',  '/contribute/articles',        'contribute\ArticleController@index'],
    ['GET',  '/contribute/articles/create', 'contribute\ArticleController@create'],
    ['POST', '/contribute/articles/create', 'contribute\ArticleController@store'],
    ['GET',  '/contribute/articles/edit/{id}',   'contribute\ArticleController@edit'],
    ['POST', '/contribute/articles/edit/{id}',   'contribute\ArticleController@update'],
    ['POST', '/contribute/articles/delete/{id}', 'contribute\ArticleController@delete'],
    /* ── READER AUTH ── */
    ['GET',  '/auth/reader/login',    'ReaderAuthController@googleRedirect'],
    ['GET',  '/auth/reader/callback', 'ReaderAuthController@callback'],
    ['GET',  '/auth/reader/logout',   'ReaderAuthController@logout'],
    ['POST', '/api/rate',             'ReaderAuthController@rate'],
    /* ══ FRONTEND ══ */
    ['GET', '/',                  'frontend\HomeController@index'],
    ['GET', '/article/{slug}',    'frontend\ArticleController@show'],
    ['GET', '/video/{slug}',      'frontend\ArticleController@show'],
    ['GET', '/breaking',          'frontend\BreakingController@index'],
    ['GET', '/tamil-news/{slug}', 'frontend\CategoryController@show'],
    ['GET', '/search',            'frontend\SearchController@index'],
    ['GET', '/sitemap-index.xml',   'frontend\SeoController@sitemapIndex'],
    ['GET', '/sitemap.xml',       'frontend\SeoController@sitemap'],
    ['GET', '/sitemap-news.xml',  'frontend\SeoController@sitemapNews'],
    ['GET', '/robots.txt',        'frontend\SeoController@robots'],

    /* ── BUSINESS ADS ── */
    ['GET',  '/admin/business-ads',                     'admin\\BusinessAdController@index'],
    ['GET',  '/admin/business-ads/create',              'admin\\BusinessAdController@create'],
    ['POST', '/admin/business-ads/store',               'admin\\BusinessAdController@store'],
    ['GET',  '/admin/business-ads/show/{id}',           'admin\\BusinessAdController@show'],
    ['GET',  '/admin/business-ads/edit/{id}',           'admin\\BusinessAdController@edit'],
    ['POST', '/admin/business-ads/update/{id}',         'admin\\BusinessAdController@update'],
    ['POST', '/admin/business-ads/approve/{id}',        'admin\\BusinessAdController@approve'],
    ['POST', '/admin/business-ads/reject/{id}',         'admin\\BusinessAdController@reject'],
    ['POST', '/admin/business-ads/confirm-payment/{id}','admin\\BusinessAdController@confirmPayment'],
    ['POST', '/admin/business-ads/delete-image/{id}',   'admin\\BusinessAdController@deleteImage'],
    ['GET',  '/admin/business-ads/cities/{id}',         'admin\\BusinessAdController@citiesByDistrict'],
    ['GET', '/lang/{lang}', 'frontend\\LangController@switch'],

    /* ── WIDGETS ── */
    ['GET',  '/admin/widgets',              'admin\\WidgetController@index'],
    ['POST', '/admin/widgets/create',       'admin\\WidgetController@create'],
    ['POST', '/admin/widgets/toggle/{id}',  'admin\\WidgetController@toggle'],
    ['POST', '/admin/widgets/reorder',      'admin\\WidgetController@reorder'],
    ['POST', '/admin/widgets/update/{id}',  'admin\\WidgetController@update'],
    ['POST', '/admin/widgets/delete/{id}',  'admin\\WidgetController@delete'],

    /* ── RATES ── */
    ['GET',  '/admin/ad-defaults',           'admin\\AdSlotController@defaults'],
    ['POST', '/admin/ad-defaults/upload',     'admin\\AdSlotController@uploadDefault'],
    ['GET',  '/api/ads/{type}',               'admin\\AdSlotController@serve'],
    ['GET',  '/admin/rates',                'admin\\RateController@index'],
    ['POST', '/admin/rates/update',         'admin\\RateController@store'],
    ['GET',  '/api/rates',                  'admin\\RateController@api'],

    /* ── POLLS ── */
    ['GET',  '/admin/polls',                'admin\\PollController@index'],
    ['GET',  '/admin/polls/create',         'admin\\PollController@create'],
    ['POST', '/admin/polls/store',          'admin\\PollController@store'],
    ['POST', '/admin/polls/toggle/{id}',    'admin\\PollController@toggle'],
    ['POST', '/admin/polls/delete/{id}',    'admin\\PollController@delete'],
    ['POST', '/poll/{id}/vote',             'frontend\\PollController@vote'],
    ['GET',  '/poll/{id}/widget',           'frontend\\PollController@widget'],

    /* ── AD PACKAGES ── */
    ['GET',  '/admin/packages',             'admin\\PackageController@index'],
    ['POST', '/admin/packages/store',       'admin\\PackageController@store'],
    ['POST', '/admin/packages/update/{id}', 'admin\\PackageController@update'],

    /* ── PERFORMANCE ── */
    ['GET',  '/admin/performance',          'admin\\PerformanceController@index'],
    ['POST', '/admin/performance/recalculate','admin\\PerformanceController@recalculate'],
    ['GET',  '/admin/performance/user/{id}','admin\\PerformanceController@user'],
];

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "api/error/index/format/json";
$route['404_override'] = 'api/error/index/format/json';

$route['admin'] = "welcome";

$route['admin/(:any)'] = 'api/admin/$1/format/json';
$route['admin/(:any)/format/(:any)'] = "api/admin/$1/format/$2";

/** USER routes **/
$route['user'] = 'api/user/index/format/json';
$route['user/format/(:any)'] = 'api/user/index/format/$1';

$route['user/access_token'] = 'api/user/access_token/format/json';
$route['user/access_token/format/(:any)'] = 'api/user/access_token/format/$1';
#$route['user/access_token/username/(:any)/password/(:any)'] = "api/user/access_token/username/$1/password/$2/format/json";
#$route['user/access_token/username/(:any)/password/(:any)/format/(:any)'] = "api/user/access_token/username/$1/password/$2/format/$3";

#$route['user/(:any)'] = 'api/user/index/userid/$1/format/json';
#$route['user/(:any)/format/(:any)'] = "api/user/index/userid/$1/format/$2";

#$route['user/access_token/(:any)'] = 'api/user/access_token/access_token/$1/format/json';
#$route['user/access_token/(:any)/format/(:any)'] = 'api/user/access_token/access_token/$1/format/$2';

$route['user/quotes'] = 'api/quotes/quotes_user/format/json';
$route['user/quotes/format/(:any)'] = 'api/quotes/quotes_user/format/$1';

$route['user/quotes/favorites'] = 'api/quotes/quotes_user_favorites/format/json';
$route['user/quotes/favorites/format/(:any)'] = 'api/quotes/quotes_user_favorites/format/$1';

/** QUOTES routes **/
$route['quotes'] = 'api/quotes/index/format/json';
$route['quotes/format/(:any)'] = 'api/quotes/index/format/$1';

$route['quotes/aftertime'] = 'api/quotes/quotes_aftertime/format/json';
$route['quotes/aftertime/format/(:any)'] = 'api/quotes/quotes_aftertime/format/$1';

$route['quotes/beforetime'] = 'api/quotes/quotes_beforetime/format/json';
$route['quotes/beforetime/format/(:any)'] = 'api/quotes/quotes_beforetime/format/$1';

/** QUOTE routes **/
$route['quote'] = 'api/quotes/quote/format/json';
$route['quote/format/(:any)'] = 'api/quotes/quote/format/$1';

$route['quote/like'] = 'api/quotes/quote_like/format/json';
$route['quote/like/format/(:any)'] = 'api/quotes/quote_like/format/$1';

$route['quote/favorite'] = 'api/quotes/quote_favorite/format/json';
$route['quote/favorite/format/(:any)'] = 'api/quotes/quote_favorite/format/$1';

$route['quote/share'] = 'api/quotes/quote_share/format/json';
$route['quote/share/format/(:any)'] = 'api/quotes/quote_share/format/$1';

$route['quote/report'] = 'api/quotes/quote_report/format/json';
$route['quote/report/format/(:any)'] = 'api/quotes/quote_report/format/$1';

$route['(:any)'] = 'api/error/index/format/json';


/* End of file routes.php */
/* Location: ./application/config/routes.php */
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['admin'] = 'admin/login';
$route['admin/forgotPassword'] = 'admin/dashboard/forgotPassword';

 $route['admin/myprofile'] = 'admin/dashboard/myprofile';
 $route['admin/updatemyprofile'] = 'admin/dashboard/updatemyprofile';

 $route['admin/user'] = 'admin/dashboard/userlist';
 
 $route['admin/registeredUser'] = 'admin/dashboard/registeredUser';
 //$route['admin/userview/(:any)'] = 'admin/dashboard/userview/$1';
 $route['admin/unregisteredUser'] = 'admin/dashboard/unregisteredUser';
   $route['admin/registeredSc'] = 'admin/dashboard/registeredSc';
 $route['admin/unregisteredSc'] = 'admin/dashboard/unregisteredSc';
 $route['admin/verifymail'] = 'admin/dashboard/verifymail';
 
 $route['admin/question'] = 'admin/dashboard/question';
 $route['admin/changepassword'] = 'admin/dashboard/changepassword';
 //$route['admin/banner'] = 'admin/dashboard/banner';
 $route['admin/category'] = 'admin/dashboard/category';
 $route['admin/subcategory/(:any)'] = 'admin/dashboard/subcategory/$1';
 
 $route['admin/registeredServiceProvider'] = 'admin/dashboard/registeredServiceProvider';
 $route['admin/unregisteredServiceProvider'] = 'admin/dashboard/unregisteredServiceProvider';
 
 $route['admin/teams'] = 'admin/dashboard/teams';
 $route['admin/services'] = 'admin/dashboard/services';
 
 $route['admin/charges'] = 'admin/dashboard/charges';

 $route['admin/registrationPayments'] = 'admin/dashboard/registrationPayments';
 $route['admin/servicePayments'] = 'admin/dashboard/servicePayments';
 
 $route['admin/updatecompanyprofile'] = 'admin/dashboard/updatecompanyprofile'; 
 $route['admin/termscondition'] = 'admin/dashboard/termscondition';
 $route['admin/aboutus'] = 'admin/dashboard/aboutus';
 $route['admin/contactus'] = 'admin/dashboard/contactus';
 
 $route['admin/teamdetails/(:num)'] = 'admin/arvind/teamdetails/$1';
 $route['admin/deleteteam/(:num)/(:num)'] = 'admin/arvind/deleteteam/$1/$2';
$route['chat'] = 'welcome/chat';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

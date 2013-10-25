MVC Theme - WordPress Plugin
==============================

#MVC Framework written to work on top of WordPress Genesis theme

This will work with a non Genesis them or may be extended into other plugins. However many of the formatting or frontend features
are designed to work with Genesis hooks a will become frustration for someone who wants the full functionality and is using a non 
Genesis theme.


## Plugin is currently in BETA
As of version 0.2.1 things are looking good and stable but more testing is a good idea. This will now update from the Github releases automatically using the standard WordPress plugin updates. Code is subject to change so it is possible a feature will no work after
running an update, but the majority of the code will either stay the same or be enhanced and debugged only. Once this is out of beta
you will be able to update with full confidence you would expect for my WordPress plugins.


##Usage
To Turn features on and off, add file mvc-config.php to your theme, copy the code from the mvc-config.php located in the plugins root and make your changes to the theme's file.

Create 3 folders inside your theme named:
* Controllers
* Models
* Views

Inside your Controller folder you may create files with names that end in Controller like so 'testController.php'.

Inside your Models folder you must create a matching file without Controller liks so 'test.php'.

Inside your newly created files create a class matching the files name like so 'class testController{}'.

When creating your controller classes be sure to extend MvcFramework like so 'class testController extends MvcFramework{}'

Inside your views folder create a matching folder like so 'test'.

Thats it. You are now setup to run Mvc in your WordPress theme.

Calls may be made from your controller to your Model like $this->test->test_method()
Calls to views may be made from your controller like $this->view('view_file_name');




More useful readme and documentation coming soon.

Up until 9/4/13 this was a private project used on many live 
WordPress sites. It is being made public in hopes of helping other developers out there who love MVC.




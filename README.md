MVC Remix - WordPress Plugin
==============================

~Current Version:0.0.1~

##MVC Framework written to work on top of WordPress Genesis theme

As you may notice from looking at the code, the plugin itself is not MVC. I know, strange right... Perhaps someday it may make sense to organize the plugin as MVC as well but for now it is far too powerful the way it is.

Instead what this plugin does is give you MVC functionality inside your theme. Using the instructions found below your will be able to organize your themes into Controllers Models and Views which work together automatically. You will be able to develop your code in a way that is organized and very easy to pawn off the entire styling phase to your designers without them ever having to dig through a bunch of PHP code they get lost in. You can also use the built in filters to turn any plugin into an MVC structure for rapid organized development. Plus there is any entire framework of useful objects built in to make many boilerplate tasks fun again. If Cake PHP and WordPress had a baby it would be MVC theme.

This will work with a non Genesis themes or may be extended into other plugins. However many of the formatting or frontend features are designed to work with Genesis hooks a will become frustrating for someone who wants the full functionality and is using a non Genesis theme.


This will update from the Github releases automatically using the standard WordPress plugin updates.


##Usage
To Turn features on and off, add file mvc-config.php to your theme, copy the code from the mvc-config.php located in the plugins root and make your changes to the theme's file.

Create 3 folders inside your theme named:
* Controllers
* Models
* Views

Inside your Controller folder you may create files with names that end in Controller like so 'testController.php'.

Inside your Models folder you must create a matching file without Controller like so 'test.php'.

Inside your newly created files create a class matching the files name like so 'class testController{}'.

When creating your controller classes be sure to extend \MVC\Core\Framework like so 'class testController extends \MVC\Core\Framework{}'

Inside your views folder create a matching folder like so 'test'.

That's it. You are now setup to run Mvc in your WordPress theme.

Calls may be made from your controller to your Model like $this->test->test_method()
Calls to views may be made from your controller like $this->view('view_file_name');





@echo off
Setlocal EnableDelayedExpansion

if NOT "%~1"=="/?" goto:skiphelp
:help
echo .
echo *****************************************
echo .
echo      MVC file creator
echo      @author Mat Lipe
echo      @since 11.27.13
echo      @usage %~0 as is will give a menu
echo      @usage %~0 "custom Post type name" build MVC scaffolding
echo      @usage %~0 tincr - build a config file for tincr
echo      @usage %~0 widget - build a widget file
echo .
echo ****************************************
goto:eof
:skiphelp


SET lib=%~dp0
for /f "tokens=1-6 delims=\" %%a in ("%lib%") do set _theme=%%f 


::If we are up too high
IF %_theme%==themes call:changeTheme 7


::IF we are down too low
IF %_theme%==lib call:changeTheme 6



::If no controller specified prompt User
if "%~1"=="" goto:menu

::Call the PHP normally
php -q "%lib%build.php" -working "%CD% " %*
goto:eof


::Change the Theme Name for different levels of installs
:changeTheme
IF %~1 == 7 goto:seven
for /f "tokens=1-7 delims=\" %%a in ("%lib%") do set _theme=%%f 
:seven
for /f "tokens=1-7 delims=\" %%a in ("%lib%") do set _theme=%%g 

goto:eof


:menu
echo. 
echo What would you like to build?
echo.
echo 1) MVC Structure
echo 2) Tincr Config File
echo 3) Widget
echo.
CHOICE /C:123 /M "Enter a selection: "
IF ERRORLEVEL 3 GOTO:promptForWidget
IF ERRORLEVEL 2 GOTO:tincr
IF ERRORLEVEL 1 GOTO:promptForController
goto:eof


::If the User does not know how to use this script
:promptForController
SET /p _controller="Enter the Name of your Post Type: "
php -q "%lib%build.php" -working "%CD% " %_controller%
goto:eof


::If the User does not know how to use this script
:promptForWidget
SET /p _widget="Enter the Name of your Widget: "
php -q "%lib%build.php" -working "%CD% " Widget %_widget%
goto:eof



::init a build of tincr
:tincr
php -q "%lib%build.php" -working "%CD% " tincr %_theme%
goto:eof
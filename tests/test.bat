@echo off
phpunit --bootstrap autoload.php TabExpansionTest 
:: --filter "/(::testCommandParams)( .*)?$/"
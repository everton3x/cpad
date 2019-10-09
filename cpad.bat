@echo off

cls

rem php -c php.ini .\cpad.php convert "teste.xlsx" "C:\Users\everton.INDEPENDENCIA\Documents\Prefeitura\2019\PAD\2019-08\pm\MES08" "C:\Users\everton.INDEPENDENCIA\Documents\Prefeitura\2019\PAD\2019-08\cm\MES08"

php -c php.ini cpad.php %*


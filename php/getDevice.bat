@echo off

cd/
cd xampp/htdocs/icloud/php


setlocal enableextensions enabledelayedexpansion
set /a "x = 0"

:while1
    if %x% == 0 (
        php socket.php
        timeout 300
        goto :while1
    )
endlocal

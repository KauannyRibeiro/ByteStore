@echo off
REM Inicia o servidor do ByteStore em http://localhost:8000
REM Use o PHP que estiver no PATH; se nao houver, usa C:\php\php.exe
cd /d "%~dp0"
where php >nul 2>nul
if %errorlevel%==0 (
    echo Servidor rodando em http://localhost:8000  ^(Ctrl+C para parar^)
    php -S localhost:8000 -t public
) else (
    echo Servidor rodando em http://localhost:8000  ^(Ctrl+C para parar^)
    "C:\php\php.exe" -S localhost:8000 -t public
)
pause

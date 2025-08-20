@echo off
echo ====================================================
echo     🔄 Convertir Certificados para IIS (PFX)
echo ====================================================
echo.

REM Verificar que existan los archivos necesarios
if not exist "certificate.crt" (
    echo ❌ ERROR: No se encuentra certificate.crt
    echo    Primero genera un certificado con la aplicacion
    pause
    exit /b 1
)

if not exist "private_key.pem" (
    echo ❌ ERROR: No se encuentra private_key.pem
    echo    Primero genera un certificado con la aplicacion
    pause
    exit /b 1
)

echo 📁 Archivos encontrados:
echo    ✅ certificate.crt
echo    ✅ private_key.pem
echo.

REM Pedir contraseña para el archivo PFX
set /p pfx_password="🔐 Ingresa una contraseña para el archivo PFX: "

if "%pfx_password%"=="" (
    echo ❌ ERROR: La contraseña no puede estar vacia
    pause
    exit /b 1
)

echo.
echo 🔄 Convirtiendo a formato PFX para IIS...

REM Comando OpenSSL para convertir a PFX
"C:\Program Files\OpenSSL-Win64\bin\openssl.exe" pkcs12 -export -out certificate_for_iis.pfx -inkey private_key.pem -in certificate.crt -password pass:%pfx_password%

if %errorlevel% equ 0 (
    echo.
    echo ✅ ¡Conversión exitosa!
    echo    📄 Archivo generado: certificate_for_iis.pfx
    echo    🔐 Contraseña: %pfx_password%
    echo.
    echo 📝 IMPORTANTE: Guarda esta contraseña, la necesitarás en IIS
    echo.
    echo 🌐 Próximos pasos:
    echo    1. Abre el Administrador de IIS
    echo    2. Ve a "Certificados de servidor"
    echo    3. Haz clic en "Importar..."
    echo    4. Selecciona: certificate_for_iis.pfx
    echo    5. Ingresa la contraseña: %pfx_password%
    echo    6. Asigna el certificado a tu sitio web
) else (
    echo.
    echo ❌ ERROR: Falló la conversión
    echo    Verifica que OpenSSL esté instalado correctamente
)

echo.
pause

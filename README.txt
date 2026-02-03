==========================================
SISTEMA DE REGISTRO DE CREDENCIALES
==========================================

VERSIÓN: 1.0
FECHA: <?php echo date('Y-m-d'); ?>
SISTEMA: SQLite (sin MySQL requerido)

==========================================
📁 ESTRUCTURA DE CARPETAS
==========================================

C:\xampp\htdocs\sistema_credenciales\
│
├── 📄 config.php                 # Configuración principal
├── 📄 database.php              # Conexión a SQLite
├── 📄 funciones.php             # Funciones del sistema
├── 📄 index.php                 # Página principal (registro)
├── 📄 verificar.php             # Verificar credenciales
├── 📄 credencial.php            # Página individual de credencial
├── 📄 lista.php                 # Listado completo
├── 📄 .htaccess                 # Configuración Apache
│
├── 📁 database/                 # Base de datos SQLite
│   └── 📄 credenciales.db      # Archivo de base de datos
│
├── 📁 uploads/                  # Archivos subidos
│   ├── 📁 fotos/               # Fotos de personas
│   └── 📁 qrcodes/             # Códigos QR generados
│
└── 📄 README.txt               # Este archivo

==========================================
🚀 INSTALACIÓN RÁPIDA
==========================================

1. COLOCAR ARCHIVOS:
   - Copia todos los archivos .php a:
     C:\xampp\htdocs\sistema_credenciales\

2. CREAR CARPETAS (se crean automáticamente):
   - El sistema creará las carpetas necesarias

3. INICIAR XAMPP:
   - Solo necesitas Apache (NO requiere MySQL)
   - Ve a: http://localhost/sistema_credenciales/

4. PERMISOS (si hay problemas):
   - Ejecuta como Administrador:
     icacls "C:\xampp\htdocs\sistema_credenciales\database" /grant Everyone:F
     icacls "C:\xampp\htdocs\sistema_credenciales\uploads" /grant Everyone:F

==========================================
🎯 FUNCIONALIDADES
==========================================

✅ REGISTRO DE PERSONAS:
   - Datos personales completos
   - Subida de fotos (JPG, PNG, GIF ≤5MB)
   - Generación automática de código (MIR-0001)
   - QR generado automáticamente

✅ VERIFICACIÓN:
   - Por código (MIR-0001)
   - Por cédula (V-12345678)
   - Escaneo de QR
   - URL directa de verificación

✅ CREDENCIALES:
   - Página individual con foto y datos
   - QR verificable
   - Modo impresión
   - Sello y firma digital

✅ LISTADO:
   - Tabla con todas las credenciales
   - Filtros por estado y búsqueda
   - Exportación a CSV
   - Estadísticas

✅ SIN MYSQL:
   - Usa SQLite (archivo local)
   - Más rápido y estable
   - Sin problemas de conexión

==========================================
🔧 CONFIGURACIÓN
==========================================

CONFIG.PHP - Ajustes principales:
----------------------------------
- SITE_URL: http://localhost/sistema_credenciales/
- MAX_FILE_SIZE: 5MB para fotos
- ALLOWED_TYPES: jpg, jpeg, png, gif
- Estados de Venezuela predefinidos

BASE DE DATOS:
----------------------------------
- Archivo: database/credenciales.db
- Se crea automáticamente
- Tablas: personas, correlativos_estado
- Índices para mejor rendimiento

==========================================
📱 CÓDIGOS DE CREDENCIAL
==========================================

FORMATO: XXX-0001
Ejemplos:
- Miranda → MIR-0001, MIR-0002, ...
- Carabobo → CAR-0001, CAR-0002, ...
- Zulia → ZUL-0001, ZUL-0002, ...

Lógica:
1. Primeras 3 letras del estado (mayúsculas)
2. Número correlativo por estado (4 dígitos)
3. Ejemplo: MIR-0001, MIR-0002, CAR-0001

==========================================
🔗 URLS DEL SISTEMA
==========================================

PRINCIPAL:
- http://localhost/sistema_credenciales/

VERIFICACIÓN:
- http://localhost/sistema_credenciales/verificar.php
- http://localhost/sistema_credenciales/verificar.php?codigo=MIR-0001

CREDENCIAL INDIVIDUAL:
- http://localhost/sistema_credenciales/credencial.php?codigo=MIR-0001
- http://localhost/sistema_credenciales/credencial.php?codigo=MIR-0001&print=true

LISTADO:
- http://localhost/sistema_credenciales/lista.php
- http://localhost/sistema_credenciales/lista.php?exportar=csv

URLS AMIGABLES:
- http://localhost/sistema_credenciales/v/MIR-0001 (verificar)
- http://localhost/sistema_credenciales/c/MIR-0001 (credencial)

==========================================
⚠️ SOLUCIÓN DE PROBLEMAS
==========================================

PROBLEMA: No se puede crear la base de datos
SOLUCIÓN:
1. Crear manualmente la carpeta: database/
2. Dar permisos de escritura
3. Recargar la página

PROBLEMA: No se suben fotos
SOLUCIÓN:
1. Verificar permisos de la carpeta uploads/
2. Verificar tamaño de archivo (≤5MB)
3. Verificar tipo de archivo (solo imágenes)

PROBLEMA: Error de PHP
SOLUCIÓN:
1. Verificar que XAMPP tenga PHP 7.4 o superior
2. Verificar que la extensión PDO_SQLite esté activada
3. Revisar el archivo error_log de Apache

PROBLEMA: No se genera QR
SOLUCIÓN:
1. El sistema usa Google Charts API
2. Requiere conexión a internet
3. Si falla, genera un QR alternativo

==========================================
🔒 SEGURIDAD
==========================================

PROTECCIÓN:
- Directorios sensibles bloqueados (.htaccess)
- Validación de tipos de archivo
- Sanitización de datos de entrada
- Protección contra inyección SQL

DATOS:
- Base de datos encriptada (SQLite)
- Fotos guardadas con nombres únicos
- QR con URLs únicas
- Sin datos sensibles expuestos

==========================================
🔄 MIGRACIÓN FUTURA A MYSQL
==========================================

SI QUIERES MIGRAR A MYSQL:

1. Exportar datos de SQLite:
   - Desde lista.php → Exportar CSV
   - O usar herramienta de SQLite

2. Crear base de datos MySQL:
   CREATE DATABASE sistema_credenciales;
   (Usar mismo SQL de las tablas)

3. Cambiar config.php:
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_credenciales');
   define('DB_USER', 'root');
   define('DB_PASS', '');

4. Importar datos del CSV

==========================================
📞 SOPORTE
==========================================

PARA REPORTAR PROBLEMAS:
1. Revisar README.txt
2. Verificar permisos de carpetas
3. Revisar archivo error_log
4. Probar con otra foto/formato

CARACTERÍSTICAS TÉCNICAS:
- PHP 7.4+ (XAMPP)
- SQLite3
- Bootstrap 5
- FontAwesome 6
- Google Charts API (QR)

==========================================
© SISTEMA DE CREDENCIALES
==========================================

Desarrollado para:
- Registro de miembros
- Control de acceso
- Verificación de identidad
- Generación de credenciales

¡Listo para usar!
Accede a: http://localhost/sistema_credenciales/
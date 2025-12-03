# 📋 GUÍA DE INSTALACIÓN - Sistema CONDUSEF

## Requisitos del Sistema

### Servidor
- PHP 8.0 o superior
- MySQL 5.7 o superior / MariaDB 10.3+
- Apache 2.4 con mod_rewrite habilitado
- Mínimo 512 MB RAM
- 100 MB de espacio en disco

### Extensiones PHP Requeridas
- PDO
- PDO_MySQL
- mysqli
- mbstring
- json
- fileinfo
- gd (opcional, para manipulación de imágenes)

## Instalación en GoDaddy Shared Hosting

### Paso 1: Preparar Archivos

1. **Descomprimir el archivo ZIP**
   - Extrae el contenido de `condusef-sistema-php.zip`
   - Verifica que tienes todos los archivos

2. **Acceder a cPanel**
   - Ingresa a tu panel de control de GoDaddy
   - Busca la opción "Administrador de archivos" o "File Manager"

3. **Subir archivos**
   - Navega a `public_html/`
   - Sube todos los archivos del sistema
   - Asegúrate de que la estructura de carpetas se mantenga
   - **IMPORTANTE:** Sube también los archivos `.htaccess`

### Paso 2: Configurar Base de Datos

1. **Crear la base de datos en cPanel**
   - Busca "Bases de datos MySQL" o "MySQL Databases"
   - Crea una nueva base de datos: `condusef_db`
   - Anota el nombre completo (puede incluir prefijo, ej: `usuario_condusef_db`)

2. **Crear usuario de base de datos**
   - En la misma sección, crea un nuevo usuario
   - Usuario: `admin1` (o el que prefieras)
   - Contraseña: Genera una segura o usa: `uIli[q+0H6@Y`
   - Anota las credenciales

3. **Asignar permisos**
   - Asocia el usuario a la base de datos
   - Otorga TODOS los privilegios

4. **Importar SQL**
   - Accede a phpMyAdmin desde cPanel
   - Selecciona la base de datos creada
   - Click en "Importar"
   - Selecciona el archivo `sql/database.sql`
   - Click en "Continuar"
   - Espera a que termine la importación (verás mensaje de éxito)

### Paso 3: Configurar Conexión a Base de Datos

1. **Editar archivo de configuración**
   - Abre el archivo `config/database.php`
   - Modifica las siguientes líneas:

   ```php
   define('DB_HOST', 'localhost');              // Usualmente localhost
   define('DB_NAME', 'condusef_db');            // Tu nombre de BD (con prefijo si aplica)
   define('DB_USER', 'admin1');                 // Tu usuario de BD
   define('DB_PASS', 'uIli[q+0H6@Y');          // Tu contraseña de BD
   ```

2. **Configurar URL del sistema**
   - Abre el archivo `config/config.php`
   - Modifica la URL base:

   ```php
   define('APP_URL', 'https://tudominio.com');  // Tu dominio real
   ```

### Paso 4: Configurar Permisos

En el administrador de archivos o via FTP, configura los siguientes permisos:

```
uploads/            → 755 (drwxr-xr-x)
uploads/documentos/ → 755
uploads/temp/       → 755
pdf/                → 755
logs/               → 755
```

**Comando SSH (si tienes acceso):**
```bash
chmod -R 755 uploads pdf logs
```

### Paso 5: Verificar Instalación

1. **Acceder al sistema**
   - Abre tu navegador
   - Visita: `https://tudominio.com/`
   - Deberías ver la página de login

2. **Iniciar sesión con credenciales por defecto**
   - Email: `admin@condusef.com`
   - Password: `admin123`

3. **Verificar acceso**
   - Si todo está bien, verás el dashboard
   - Si hay error de conexión, revisa el archivo `config/database.php`

### Paso 6: Configuración Post-Instalación

1. **Cambiar contraseña del administrador (IMPORTANTE)**
   - Ve a "Mi Perfil" en el menú de usuario
   - Cambia la contraseña por defecto inmediatamente

2. **Configurar HTTPS (Recomendado)**
   - En GoDaddy cPanel, busca "SSL/TLS"
   - Activa el certificado SSL gratuito
   - Edita `.htaccess` y descomenta estas líneas:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. **Crear usuarios adicionales**
   - Como admin, ve a "Usuarios" → "Nuevo Usuario"
   - Crea cuentas para tu equipo

4. **Verificar envío de emails (opcional)**
   - Si quieres notificaciones por email, configura SMTP
   - Contacta a GoDaddy para obtener los datos SMTP

## Solución de Problemas Comunes

### Error: "Error al conectar con la base de datos"

**Soluciones:**
1. Verifica que las credenciales en `config/database.php` sean correctas
2. Confirma que el usuario tiene permisos sobre la base de datos
3. Verifica que el host sea correcto (usualmente `localhost`)
4. En algunos casos, GoDaddy usa un host específico como `127.0.0.1` o `localhost:3306`

### Error: "Internal Server Error 500"

**Soluciones:**
1. Verifica que el archivo `.htaccess` se haya subido correctamente
2. Comprueba los permisos de archivos y carpetas
3. Revisa el log de errores en cPanel → "Error Log"
4. Asegúrate de que PHP 8.0+ esté activo

### Páginas sin estilos (sin CSS)

**Soluciones:**
1. Verifica que la URL en `config/config.php` sea correcta
2. Abre la consola del navegador (F12) y busca errores 404
3. Verifica que los archivos en `assets/` se hayan subido correctamente

### No se pueden subir documentos

**Soluciones:**
1. Verifica permisos de la carpeta `uploads/`
2. Asegúrate que el `.htaccess` en uploads esté presente
3. Verifica el límite de subida en PHP (debe ser 10MB mínimo)

### Las imágenes de perfil no se muestran

**Soluciones:**
1. Verifica permisos de `uploads/`
2. Comprueba que GD esté instalado en PHP
3. Revisa los logs de errores

## Mantenimiento

### Respaldos

**Base de datos:**
- Desde phpMyAdmin: "Exportar" → Elegir formato SQL → Descargar
- Recomendado: Hacer respaldo semanal

**Archivos:**
- Respaldar carpeta `uploads/` regularmente
- Descargar via FTP o File Manager

### Actualización de datos

**Agregar más aseguradoras:**
```sql
INSERT INTO aseguradoras (nombre, telefono, email, sitio_web, activa)
VALUES ('Nombre Aseguradora', '55-1234-5678', 'contacto@aseg.com', 'https://aseg.com', 1);
```

**Resetear contraseña de usuario:**
```sql
-- Contraseña: admin123
UPDATE usuarios
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@condusef.com';
```

## Seguridad Adicional

1. **Eliminar archivo de instalación (si existe)**
   ```bash
   rm install.php
   ```

2. **Proteger archivos sensibles**
   - Los `.htaccess` ya protegen las carpetas críticas
   - No compartas credenciales de base de datos

3. **Configurar respaldos automáticos**
   - GoDaddy ofrece respaldos automáticos
   - Actívalos en cPanel

4. **Mantener PHP actualizado**
   - En cPanel → "Select PHP Version"
   - Usa la versión más reciente disponible

## Soporte

Para soporte técnico:
- **Email:** soporte@maldonadoyasociados.com
- **Teléfono:** [Tu teléfono]
- **Documentación:** Consulta README.md

## Recursos Adicionales

- [Documentación de PHP](https://www.php.net/manual/es/)
- [Manual de MySQL](https://dev.mysql.com/doc/)
- [Soporte GoDaddy](https://www.godaddy.com/help)

---

**Versión del Sistema:** 1.0.0
**Última Actualización:** Diciembre 2025
**Desarrollado por:** Maldonado y Asociados

# 🛡️ Sistema CONDUSEF - Gestión de Casos

Sistema de gestión de casos para la Comisión Nacional para la Protección y Defensa de los Usuarios de Servicios Financieros (CONDUSEF) desarrollado para Maldonado y Asociados.

## 📌 Características Principales

### ✅ Gestión Completa de Casos
- ✔️ CRUD completo de casos con estados (Nuevo, En Proceso, Presentado UNE, Presentado CONDUSEF, Conciliación, Resuelto, Cerrado)
- ✔️ Sistema de prioridades (Baja, Media, Alta, Urgente)
- ✔️ Timeline de actividades y seguimiento
- ✔️ Asignación de casos a usuarios
- ✔️ Fechas límite y alertas de vencimiento
- ✔️ Búsqueda y filtros avanzados

### 👥 Gestión de Clientes
- ✔️ Registro completo de clientes con datos de contacto
- ✔️ CURP, RFC y datos fiscales
- ✔️ Historial de casos por cliente
- ✔️ Búsqueda rápida

### 🏢 Catálogo de Aseguradoras
- ✔️ 18 aseguradoras mexicanas precargadas
- ✔️ Información de contacto completa
- ✔️ Sistema de activación/desactivación

### 📄 Gestión de Documentos
- ✔️ Carga de archivos (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG)
- ✔️ Límite de 10MB por archivo
- ✔️ Categorización automática
- ✔️ Descarga segura
- ✔️ Protección contra ejecución de scripts

### 📋 Cuestionario de 279 Campos
- ✔️ Formulario estructurado por secciones
- ✔️ Guardado automático con AJAX
- ✔️ Barra de progreso
- ✔️ Almacenamiento en JSON

### 📊 Generación de PDFs
- ✔️ Escrito de reclamación (formato CONDUSEF)
- ✔️ Cronología del caso
- ✔️ Listado de anexos
- ✔️ Reportes personalizados

### 👤 Sistema de Usuarios
- ✔️ Autenticación segura con bcrypt
- ✔️ 4 roles: Administrador, Abogado, Asistente, Cliente
- ✔️ Rate limiting (5 intentos de login)
- ✔️ Bloqueo temporal tras intentos fallidos
- ✔️ Gestión de sesiones seguras

### 🔒 Seguridad Implementada
- ✔️ PDO con prepared statements (protección SQL injection)
- ✔️ Tokens CSRF en todos los formularios
- ✔️ Sanitización y validación de datos (XSS)
- ✔️ Passwords hasheados con bcrypt
- ✔️ Headers de seguridad HTTP
- ✔️ .htaccess para protección de archivos
- ✔️ Logs de auditoría completos
- ✔️ Validación de tipos de archivo y tamaño

### 📈 Dashboard y Reportes
- ✔️ Estadísticas en tiempo real
- ✔️ Casos por estado
- ✔️ Montos reclamados y recuperados
- ✔️ Actividad reciente
- ✔️ Casos próximos a vencer

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8+ puro (sin frameworks)
- **Base de Datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Bootstrap 5, jQuery
- **PDFs:** FPDF
- **Servidor:** Apache 2.4
- **Hosting:** Compatible con GoDaddy Shared Hosting

## 📦 Estructura del Proyecto

```
condusef-sistema-php/
├── index.php                  # Dashboard principal
├── login.php                  # Página de login
├── logout.php                 # Cerrar sesión
├── .htaccess                  # Configuración Apache y seguridad
├── config/
│   ├── database.php          # Conexión PDO a MySQL
│   └── config.php            # Configuraciones generales
├── includes/
│   ├── header.php            # Header y navegación
│   ├── sidebar.php           # Menú lateral
│   ├── footer.php            # Footer y scripts
│   ├── security.php          # Funciones de seguridad
│   └── functions.php         # Funciones auxiliares
├── pages/
│   ├── casos/                # Módulo de casos
│   │   ├── lista.php
│   │   ├── crear.php
│   │   ├── editar.php
│   │   └── ver.php
│   ├── clientes/             # Módulo de clientes
│   │   ├── lista.php
│   │   ├── crear.php
│   │   ├── editar.php
│   │   └── ver.php
│   ├── documentos/           # Gestión de documentos
│   ├── aseguradoras/         # Catálogo de aseguradoras
│   └── cuestionarios/        # Formulario de 279 campos
├── api/
│   ├── auth/                 # APIs de autenticación
│   ├── casos/                # APIs de casos
│   ├── clientes/             # APIs de clientes
│   ├── documentos/           # APIs de documentos
│   └── cuestionarios/        # APIs de cuestionarios
├── assets/
│   ├── css/                  # Estilos personalizados
│   ├── js/                   # Scripts JavaScript
│   └── img/                  # Imágenes
├── uploads/
│   ├── documentos/           # Archivos subidos
│   ├── temp/                 # Archivos temporales
│   └── .htaccess             # Protección de uploads
├── pdf/                      # PDFs generados
├── sql/
│   └── database.sql          # Estructura y datos iniciales
└── logs/                     # Logs del sistema
```

## 🚀 Instalación Rápida

### 1. Requisitos
- PHP 8.0+
- MySQL 5.7+
- Apache con mod_rewrite

### 2. Subir archivos
- Extrae el ZIP
- Sube todos los archivos a `public_html/` via FTP o File Manager

### 3. Crear base de datos
- En cPanel → MySQL Databases
- Crea la base de datos `condusef_db`
- Crea el usuario y asigna permisos
- Importa `sql/database.sql` via phpMyAdmin

### 4. Configurar
- Edita `config/database.php` con tus credenciales
- Edita `config/config.php` con tu URL

### 5. Acceder
- URL: `https://tudominio.com/`
- Usuario: `admin@condusef.com`
- Contraseña: `admin123`

**📖 Para instrucciones detalladas, consulta [INSTALACION.md](INSTALACION.md)**

## 👥 Usuarios por Defecto

| Email                   | Password  | Rol            |
|------------------------|-----------|----------------|
| admin@condusef.com     | admin123  | Administrador  |

**⚠️ IMPORTANTE:** Cambia la contraseña inmediatamente después del primer login.

## 🏢 Aseguradoras Precargadas

El sistema incluye 18 aseguradoras mexicanas:
1. AXA Seguros
2. GNP Seguros
3. Qualitas
4. Mapfre
5. Seguros Banorte
6. HDI Seguros
7. Inbursa
8. Zurich
9. Chubb Seguros
10. Atlas
11. ANA Seguros
12. Afirme Seguros
13. Primero Seguros
14. Plan Seguro
15. Monterrey New York Life
16. Seguros Sura
17. El Aguila
18. Metlife

## 📊 Base de Datos

### Tablas principales:
- `usuarios` - Gestión de usuarios y roles
- `clientes` - Información de clientes
- `aseguradoras` - Catálogo de aseguradoras
- `casos` - Casos y reclamaciones
- `cuestionarios` - Datos del formulario de 279 campos
- `documentos` - Archivos adjuntos
- `seguimientos` - Timeline de actividades
- `historial_comunicaciones` - Registro de comunicaciones
- `auditoria` - Logs de auditoría

## 🔐 Seguridad

El sistema implementa las mejores prácticas de seguridad:

1. **SQL Injection:** PDO con prepared statements
2. **XSS:** htmlspecialchars en todas las salidas
3. **CSRF:** Tokens en todos los formularios
4. **Passwords:** Bcrypt con cost 12
5. **Sesiones:** httponly, secure, regeneración periódica
6. **Uploads:** Validación de tipo, tamaño y .htaccess
7. **Rate Limiting:** 5 intentos de login, bloqueo 15 min
8. **Headers:** X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
9. **Auditoría:** Registro de todas las acciones importantes

## 🎨 Interfaz

- **Framework CSS:** Bootstrap 5
- **Iconos:** Bootstrap Icons
- **Colores:** Azul primario (#0284c7)
- **Responsive:** Adaptable a móvil, tablet y desktop
- **JavaScript:** jQuery para interactividad

## 📝 Módulos Funcionales

### Casos
- Crear, editar, ver y listar casos
- Estados y prioridades
- Asignación a usuarios
- Timeline de actividades
- Seguimiento de montos

### Clientes
- Registro completo de datos personales
- CURP y RFC
- Dirección completa
- Historial de casos

### Documentos
- Upload seguro de archivos
- Categorización
- Vista previa
- Descarga controlada

### Cuestionario
- 279 campos estructurados
- Guardado automático
- Progreso visual
- Validaciones

### Reportes
- Dashboard con estadísticas
- Filtros avanzados
- Exportación a PDF
- Gráficas y métricas

## 🆘 Soporte y Ayuda

### Problemas Comunes

**Error de conexión a BD:**
- Verifica credenciales en `config/database.php`
- Confirma que el usuario tenga permisos

**Error 500:**
- Revisa `.htaccess`
- Verifica permisos de carpetas
- Consulta logs de errores

**No se suben archivos:**
- Verifica permisos de `uploads/` (755)
- Confirma límite de PHP (10MB+)

### Contacto
- **Desarrollado para:** Maldonado y Asociados
- **Estudiante:** Daniel (8vo semestre, FIME-UANL)
- **Email:** soporte@maldonadoyasociados.com

## 📄 Licencia

Sistema propietario desarrollado para uso exclusivo de Maldonado y Asociados.

## 🔄 Actualizaciones

**Versión 1.0.0** - Diciembre 2025
- Lanzamiento inicial del sistema
- Todos los módulos funcionales
- Sistema de seguridad completo
- Documentación completa

## 🎯 Roadmap Futuro

Posibles mejoras futuras:
- [ ] Notificaciones por email
- [ ] Exportación a Excel
- [ ] App móvil
- [ ] Integración con API de CONDUSEF
- [ ] Firma electrónica de documentos
- [ ] Chat interno entre usuarios
- [ ] Dashboard con gráficas avanzadas

---

**Sistema CONDUSEF v1.0.0**
© 2025 Maldonado y Asociados
Desarrollado con ❤️ para la gestión eficiente de casos CONDUSEF

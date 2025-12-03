# 📄 Configuración de FPDF para Generación de PDFs

## ¿Qué es FPDF?

FPDF es una biblioteca PHP que permite generar documentos PDF sin necesidad de Composer ni dependencias externas. Es perfecta para hosting compartido como GoDaddy.

## Descarga e Instalación

### Paso 1: Descargar FPDF

1. **Descarga directa:**
   - Visita: http://www.fpdf.org/
   - Click en "Download"
   - Descarga la última versión (1.86 o superior)

2. **Archivo descargado:**
   - `fpdf186.zip` (o la versión más reciente)

### Paso 2: Extraer e Instalar

1. **Extraer el ZIP:**
   - Descomprime `fpdf186.zip`
   - Encontrarás una carpeta `fpdf186/`

2. **Subir al servidor:**
   - Copia la carpeta completa `fpdf186/` al directorio raíz del sistema
   - Renombra la carpeta a `fpdf/` para simplificar
   - Ruta final: `condusef-sistema-php/fpdf/`

3. **Estructura esperada:**
   ```
   condusef-sistema-php/
   ├── fpdf/
   │   ├── fpdf.php          ← Archivo principal
   │   ├── font/             ← Fuentes
   │   └── ...
   ```

### Paso 3: Verificar Instalación

Crea un archivo de prueba `test-pdf.php`:

```php
<?php
require_once __DIR__ . '/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, '¡FPDF funciona correctamente!');
$pdf->Output();
```

Abre `https://tudominio.com/test-pdf.php` en tu navegador. Si ves un PDF, ¡está funcionando!

## Uso en el Sistema CONDUSEF

### Estructura de PDFs

El sistema genera los siguientes tipos de PDF:

1. **Escrito de Reclamación** (`pdf/generar-escrito.php`)
2. **Cronología del Caso** (`pdf/generar-cronologia.php`)
3. **Listado de Anexos** (`pdf/generar-anexos.php`)
4. **Reporte General** (`pdf/generar-reporte.php`)

### Ejemplo: Generar Escrito de Reclamación

Archivo: `pdf/generar-escrito.php`

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

session_start();
requireLogin();

// Obtener ID del caso
$casoId = (int)($_GET['caso_id'] ?? 0);

if (!$casoId) {
    die('ID de caso inválido');
}

// Obtener datos del caso
$sql = "SELECT c.*, cl.nombre_completo as cliente_nombre, cl.curp, cl.domicilio_calle,
        cl.domicilio_numero, cl.domicilio_colonia, cl.domicilio_ciudad, cl.domicilio_estado,
        cl.domicilio_cp, a.nombre as aseguradora_nombre
        FROM casos c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        LEFT JOIN aseguradoras a ON c.aseguradora_id = a.id
        WHERE c.id = ?";

$caso = queryOne($sql, [$casoId]);

if (!$caso) {
    die('Caso no encontrado');
}

// Crear PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'ESCRITO DE RECLAMACION', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Datos del cliente
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'DATOS DEL RECLAMANTE', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Nombre: ' . utf8_decode($caso['cliente_nombre']), 0, 1);
$pdf->Cell(0, 6, 'CURP: ' . $caso['curp'], 0, 1);

$direccion = implode(', ', array_filter([
    $caso['domicilio_calle'],
    $caso['domicilio_numero'],
    $caso['domicilio_colonia'],
    $caso['domicilio_ciudad'],
    $caso['domicilio_estado'],
    'C.P. ' . $caso['domicilio_cp']
]));
$pdf->Cell(0, 6, utf8_decode('Dirección: ' . $direccion), 0, 1);
$pdf->Ln(5);

// Datos de la aseguradora
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'DATOS DE LA INSTITUCION', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Aseguradora: ' . utf8_decode($caso['aseguradora_nombre']), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Número de Póliza: ') . $caso['numero_poliza'], 0, 1);
$pdf->Cell(0, 6, 'Tipo de Seguro: ' . utf8_decode($caso['tipo_seguro']), 0, 1);
$pdf->Ln(5);

// Hechos
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'HECHOS', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, utf8_decode($caso['descripcion']));
$pdf->Ln(5);

// Monto reclamado
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'MONTO RECLAMADO', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Monto: ' . formatMoney($caso['monto_reclamado']), 0, 1);

// Salida
$pdf->Output('D', 'Escrito_Reclamacion_' . $caso['folio'] . '.pdf');
```

### Ejemplo: Generar Cronología

Archivo: `pdf/generar-cronologia.php`

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

session_start();
requireLogin();

$casoId = (int)($_GET['caso_id'] ?? 0);

if (!$casoId) {
    die('ID de caso inválido');
}

// Obtener caso
$caso = queryOne("SELECT * FROM casos WHERE id = ?", [$casoId]);

// Obtener seguimientos
$sql = "SELECT s.*, u.nombre as usuario_nombre
        FROM seguimientos s
        LEFT JOIN usuarios u ON s.realizado_por = u.id
        WHERE s.caso_id = ?
        ORDER BY s.fecha_actividad ASC";

$seguimientos = query($sql, [$casoId]);

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('CRONOLOGÍA DEL CASO'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Folio: ' . $caso['folio'], 0, 1);
$pdf->Ln(5);

// Tabla de seguimientos
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 7, 'Fecha', 1);
$pdf->Cell(40, 7, 'Tipo', 1);
$pdf->Cell(115, 7, utf8_decode('Descripción'), 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 9);
foreach ($seguimientos as $seg) {
    $fecha = date('d/m/Y', strtotime($seg['fecha_actividad']));
    $pdf->Cell(35, 6, $fecha, 1);
    $pdf->Cell(40, 6, utf8_decode($seg['tipo_actividad']), 1);
    $pdf->Cell(115, 6, utf8_decode(substr($seg['titulo'], 0, 60)), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'Cronologia_' . $caso['folio'] . '.pdf');
```

## Fuentes y Caracteres Especiales

### Problema con Acentos

Si ves caracteres raros en lugar de acentos, usa `utf8_decode()`:

```php
$pdf->Cell(0, 10, utf8_decode('México, Descripción, Número'));
```

### Agregar Fuentes Personalizadas

1. Descarga fuentes TrueType (.ttf)
2. Usa el script `makefont.php` de FPDF para convertirlas
3. Copia los archivos generados a `fpdf/font/`
4. Úsalas en tu código:

```php
$pdf->AddFont('MiFuente', '', 'mifuente.php');
$pdf->SetFont('MiFuente', '', 12);
```

## Configuración de Márgenes y Tamaño

```php
// Cambiar orientación (P = vertical, L = horizontal)
$pdf = new FPDF('L', 'mm', 'A4');

// Ajustar márgenes
$pdf->SetMargins(20, 20, 20);

// Tamaño de página personalizado (ancho, alto en mm)
$pdf = new FPDF('P', 'mm', array(210, 297));
```

## Funciones Útiles

### Saltos de página automáticos
```php
$pdf->SetAutoPageBreak(true, 15); // 15mm de margen inferior
```

### Colores
```php
$pdf->SetTextColor(255, 0, 0);    // Rojo
$pdf->SetFillColor(200, 220, 255); // Azul claro
$pdf->SetDrawColor(0, 0, 0);       // Negro (bordes)
```

### Imágenes
```php
$pdf->Image('logo.png', 10, 10, 30); // x, y, width
```

## Recursos Adicionales

- **Documentación oficial:** http://www.fpdf.org/en/doc/
- **Tutorial:** http://www.fpdf.org/en/tutorial/
- **Scripts útiles:** http://www.fpdf.org/en/script/

## Soporte

Si tienes problemas con FPDF:
1. Verifica que el archivo `fpdf/fpdf.php` existe
2. Confirma que tienes permisos de lectura en la carpeta
3. Revisa los logs de error de PHP
4. Consulta la documentación oficial

---

**Nota:** FPDF es una biblioteca muy ligera y no requiere instalación compleja. Es ideal para GoDaddy Shared Hosting.

# Portafolio de Entregas — Programación Web

> **Alumno:** Esteban Rafael Avila Sanchez
> **No. Control:** 22300193
> **Grupo:** 8F
> **Institución:** Centro de Enseñanza Técnica Industrial (CETI) — Plantel TNL
> **Servidor:** `proyectosinformaticatnl.ceti.mx`
> **Ruta remota:** `/datos/sitios/mueblesweb`

---

## Estructura del repositorio

```
22300193/
├── index.html                  ← Panel de entregas principal
├── README.md
├── fpdf185/                    ← Librería FPDF para generación de PDFs
├── .vscode/
│   ├── settings.json
│   └── sftp.json               ← Configuración de despliegue SFTP
└── public/
    ├── assets/                 ← CSS, JS e imágenes compartidos
    │   ├── css/
    │   ├── js/
    │   └── img/
    ├── WEB1/
    │   ├── web1.html           ← Menú de WEB1
    │   ├── INTEGRADORA/        ← Proyecto integrador WEB1
    │   └── PRACTICAS/          ← Prácticas WEB1
    └── WEB2/
        ├── web2.html           ← Menú de WEB2
        ├── INTEGRADORA/        ← Proyecto integrador WEB2 (MotoStore)
        └── PRACTICAS/          ← Prácticas WEB2
```

---

## Panel de Entregas (`index.html`)

Página de inicio que centraliza el acceso a todas las materias. Muestra:
- Datos del alumno y del servidor
- Acceso a **Programación Web 1** y **Programación Web 2**
- Logo institucional del CETI

---

## WEB2 — Proyecto Integrador: MotoStore

Tienda en línea de motocicletas con carrito de compras, pago con PayPal (Sandbox) y generación de documentos PDF.

### Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | PHP (sin framework), MySQLi |
| Frontend | HTML5, CSS3, Bootstrap 5.3, Google Fonts |
| Pagos | PayPal SDK JS + REST API v2 (Sandbox) |
| PDF | FPDF 1.85 |
| Datos temporales | XML (`carrito.xml`) |
| Despliegue | SFTP automático vía VS Code |

### Flujo de usuario

```
login.php → index.php → galeria.php → agregar.php
                                          ↓
                                     carrito.php
                                          ↓
                                     checkout.php  ←── PayPal SDK
                                          ↓
                              paypal_create_order.php
                                          ↓
                              paypal_capture_order.php
                                          ↓
                                   confirmacion.php
                                     ↙        ↘
                           generar_factura.php  generar_ticket.php
```

### Archivos del proyecto

| Archivo | Descripción |
|---|---|
| `index.php` | Landing page con hero, carrusel y acceso al catálogo |
| `login.php` | Autenticación con sesión PHP |
| `registro.php` | Registro de nuevos usuarios |
| `logout.php` | Cierre de sesión |
| `galeria.php` | Catálogo de motocicletas con imágenes desde BD |
| `agregar.php` | Agrega productos al carrito (escribe en `carrito.xml`) |
| `carrito.php` | Vista del carrito, modificar cantidades y eliminar ítems |
| `checkout.php` | Formulario de datos de contacto + botones de PayPal |
| `paypal_create_order.php` | Endpoint: crea la orden en PayPal REST API |
| `paypal_capture_order.php` | Endpoint: captura el pago y guarda la orden en BD |
| `confirmacion.php` | Resumen de compra con desglose de IVA y botones de descarga |
| `generar_factura.php` | Genera PDF de factura en hoja carta (Letter) con IVA desglosado |
| `generar_ticket.php` | Genera PDF de ticket estilo térmico (80 mm) con IVA desglosado |
| `paypal_config.php` | Helpers de comunicación con PayPal (token, crear orden, capturar) |
| `conectbd.php` | Conexión a la base de datos MySQL |
| `imagen.php` | Sirve imágenes BLOB almacenadas en la BD como respuesta HTTP |
| `carrito.xml` | Archivo temporal que persiste el estado del carrito |
| `.env` | Variables de entorno con credenciales PayPal (no se sube al repo) |

### Variables de entorno (`.env`)

```env
PAYPAL_CLIENT_ID=tu_client_id_aqui
PAYPAL_CLIENT_SECRET=tu_client_secret_aqui
PAYPAL_MODE=sandbox
```

> El archivo `.env` está en `.gitignore` y en la lista de ignorados del SFTP. **Nunca se sube al repositorio ni al servidor de forma automática.**

### Base de datos

Se requieren dos archivos SQL para crear el esquema completo:

#### `schema_integradora.sql`

```sql
-- Tabla de usuarios
CREATE TABLE `usuarios` (
  `idU`         INT(3) AUTO_INCREMENT PRIMARY KEY,
  `usuarioU`    VARCHAR(50)  NOT NULL,
  `contrasenaU` VARCHAR(255) NOT NULL
);

-- Tabla de productos (imágenes como MEDIUMBLOB)
CREATE TABLE `productos` (
  `idP`         INT(3) AUTO_INCREMENT PRIMARY KEY,
  `nombreP`     VARCHAR(100) NOT NULL,
  `marcaP`      VARCHAR(50)  NOT NULL,
  `imagenP`     MEDIUMBLOB,
  `tipoImagenP` VARCHAR(20)  NOT NULL DEFAULT 'image/jpg',
  `existenciaP` INT(11)      NOT NULL DEFAULT 0,
  `precioP`     DECIMAL(10,2) NOT NULL
);
```

Incluye datos de 10 motocicletas (BMW, KTM, Yamaha, Kawasaki, Honda, Ducati, Suzuki, CFMoto) con precios en MXN.

#### `schema_ordenes.sql`

```sql
-- Tabla de órdenes de compra
CREATE TABLE `ordenes` (
  `idOrden`         INT(11)       AUTO_INCREMENT PRIMARY KEY,
  `idUsuario`       INT(3)        NOT NULL,
  `nombre`          VARCHAR(100)  NOT NULL,
  `apellido`        VARCHAR(100)  NOT NULL,
  `telefono`        VARCHAR(20)   NOT NULL,
  `email`           VARCHAR(100)  NOT NULL,
  `notas`           TEXT,
  `total`           DECIMAL(10,2) NOT NULL,
  `paypal_order_id` VARCHAR(100)  DEFAULT NULL,
  `estado`          VARCHAR(20)   NOT NULL DEFAULT 'pendiente',
  `fecha`           DATETIME      DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de detalle de cada orden
CREATE TABLE `orden_detalle` (
  `idDetalle` INT(11)       AUTO_INCREMENT PRIMARY KEY,
  `idOrden`   INT(11)       NOT NULL,
  `idP`       INT(3)        NOT NULL,
  `nombreP`   VARCHAR(100)  NOT NULL,
  `precioP`   DECIMAL(10,2) NOT NULL,
  `cantidad`  INT(11)       NOT NULL
);
```

### Integración con PayPal

La comunicación con la REST API de PayPal se realiza en `paypal_config.php` usando `file_get_contents` con `stream_context` (SSL verify deshabilitado para compatibilidad con el servidor CETI). cURL se usa como fallback si `allow_url_fopen` está desactivado.

Flujo de la API:
1. **Token** — `POST /v1/oauth2/token` con credenciales en Base64
2. **Crear orden** — `POST /v2/checkout/orders` con el total en MXN
3. **Capturar** — `POST /v2/checkout/orders/{id}/capture`

### Generación de PDFs

Ambos documentos calculan el desglose de IVA sobre el total almacenado en BD:

```
Subtotal sin IVA = total / 1.16
IVA (16%)        = total − subtotal sin IVA
Total            = valor en BD (sin cambios)
```

| Documento | Archivo | Formato | Nombre del PDF |
|---|---|---|---|
| Factura | `generar_factura.php` | Carta (Letter) | `factura_000001.pdf` |
| Ticket | `generar_ticket.php` | 80 mm × dinámico | `ticket_000001.pdf` |

La **factura** incluye logo, dos columnas de datos, tabla con precios sin IVA, filas de subtotal/IVA/total y nota de recogida.
El **ticket** es estilo impresora térmica: fondo blanco, logo centrado, columnas compactas y la misma sección de totales.

### Catálogo de productos

| Modelo | Marca | Precio MXN | Existencia |
|---|---|---:|---:|
| BMW S 1000 RR | BMW Motorrad | $589,000 | 8 |
| KTM 1290 Super Duke | KTM | $420,000 | 12 |
| Yamaha MT-09 | Yamaha | $189,000 | 10 |
| Kawasaki Ninja 400 | Kawasaki | $112,000 | 15 |
| Yamaha R1 | Yamaha | $490,000 | 6 |
| Honda CB650R | Honda | $148,000 | 9 |
| Ducati Panigale V4 | Ducati | $840,000 | 4 |
| Suzuki GSX-R750 | Suzuki | $210,000 | 7 |
| Kawasaki Z900 | Kawasaki | $175,000 | 5 |
| CFMoto 675 NK | CFMoto | $75,000 | 20 |

---

## WEB1 — Proyecto Integrador

Ubicación: `public/WEB1/INTEGRADORA/integradora1.html`

Proyecto integrador de la materia Programación Web 1, desarrollado con HTML, CSS y JavaScript puros (sin framework). Accesible desde el menú `public/WEB1/web1.html`.

---

## WEB2 — Prácticas

Ubicación: `public/WEB2/PRACTICAS/`

| Carpeta | Contenido |
|---|---|
| `CRUD_XML/` | CRUD completo usando XML como fuente de datos en PHP |

---

## WEB1 — Prácticas

Ubicación: `public/WEB1/PRACTICAS/`

| Carpeta | Contenido |
|---|---|
| `CARRUSEL/` | Práctica de carrusel de imágenes en JavaScript puro |
| `METG_METP/` | Práctica de métodos GET y POST con formularios HTML |

---

## Assets compartidos (`public/assets/`)

```
assets/
├── css/
│   ├── _variables.css          ← Variables CSS globales (colores, fuentes)
│   ├── styles.css              ← Estilos base del panel de entregas
│   ├── index.css               ← Estilos del panel principal
│   ├── integradora2.css        ← Estilos base de MotoStore WEB2
│   ├── integradora_*.css       ← Estilos por página de MotoStore
│   ├── integradora1.css        ← Estilos del proyecto WEB1
│   ├── web1.css / web2.css     ← Estilos de los menús de materia
│   └── ...
├── js/
│   ├── app.js                  ← Lógica compartida
│   ├── utils.js                ← Utilidades reutilizables
│   ├── integradora_*.js        ← JS por página de MotoStore
│   ├── integradora1.js         ← JS del proyecto WEB1
│   └── ...
└── img/
    ├── logo-ceti.png           ← Logo institucional CETI
    ├── moto_bmw.jpg
    ├── moto_ktm.jpg
    ├── moto_mt.jpg
    ├── moto_ninja400.jpg
    └── moto_yamaha.jpg
```

---

## Librería FPDF (`fpdf185/`)

FPDF versión 1.85 — librería PHP para generación de PDFs sin dependencias externas.
Licencia: libre para uso comercial y no comercial (ver `fpdf185/license.txt`).
Documentación local: `fpdf185/doc/index.htm`

---

## Despliegue

El proyecto se despliega automáticamente al guardar archivos gracias a la extensión **SFTP** de VS Code configurada en `.vscode/sftp.json`:

| Parámetro | Valor |
|---|---|
| Host | `proyectosinformaticatnl.ceti.mx` |
| Protocolo | SFTP (puerto 22) |
| Ruta remota | `/datos/sitios/mueblesweb` |
| Upload on save | Activado |
| Auto upload | Activado |

Archivos **excluidos** del despliegue automático: `.vscode/`, `.git/`, `.env`, `README.md`, `*.log`, `node_modules/`.

---

## Requisitos del servidor

- PHP 5.5 o superior
- Extensión `mysqli` habilitada
- `allow_url_fopen = On` (necesario para comunicación con PayPal sin cURL)
- MySQL / MariaDB
- FPDF disponible en `../../../fpdf185/fpdf.php` relativo a la carpeta del proyecto

---

*Programación Web 2 — Mtra. Patricia Torres — CETI TNL — 2025*

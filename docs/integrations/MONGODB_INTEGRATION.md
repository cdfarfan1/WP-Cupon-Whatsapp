# Manual de Integración con MongoDB para WP-Cupon-WhatsApp

## Índice
1. [Introducción](#introducción)
2. [Requisitos Previos](#requisitos-previos)
3. [Instalación](#instalación)
4. [Configuración](#configuración)
5. [Funcionalidades](#funcionalidades)
6. [Sincronización](#sincronización)
7. [Exportación de Datos](#exportación-de-datos)
8. [Solución de Problemas](#solución-de-problemas)
9. [Registro de Cambios](#registro-de-cambios)

## Introducción

La integración con MongoDB permite:
- Sincronización bidireccional entre WordPress y MongoDB
- Respaldo de datos en tiempo real
- Exportación de datos en múltiples formatos
- Análisis avanzado de datos usando las capacidades de MongoDB

## Requisitos Previos

1. Servidor MongoDB (versión 4.0 o superior)
2. Extensión PHP MongoDB instalada (`php-mongodb`)
3. Permisos de administrador en WordPress
4. Credenciales de acceso a MongoDB

### Instalación de la Extensión MongoDB

```bash
# Para Ubuntu/Debian
sudo apt-get install php-mongodb

# Para CentOS/RHEL
sudo yum install php-mongodb

# Para Windows con XAMPP
pecl install mongodb

# Verificar la instalación
php -m | grep mongodb
```

## Instalación

1. La funcionalidad MongoDB viene incluida en el plugin WP-Cupon-WhatsApp
2. No requiere instalación adicional más allá de los requisitos previos

## Configuración

### En WordPress

1. Ir a "Ajustes" > "WP Cupón WhatsApp"
2. Localizar la sección "Configuración de MongoDB"
3. Configurar:
   - Habilitar MongoDB
   - URI de conexión
   - Nombre de la base de datos
   - Sincronización automática

### Formato de la URI de Conexión

```
mongodb://[usuario:contraseña@]host[:puerto][/base_datos]
```

Ejemplos:
- Local: `mongodb://localhost:27017`
- Con autenticación: `mongodb://usuario:contraseña@localhost:27017`
- Atlas: `mongodb+srv://usuario:contraseña@cluster.mongodb.net/base_datos`

## Funcionalidades

### 1. Sincronización Automática

La sincronización automática actualiza MongoDB cuando:
- Se crea un nuevo cupón
- Se canjea un cupón
- Se actualiza el estado de un canje
- Se modifica información del comercio

### 2. Sincronización Manual

```php
// Código para sincronización manual
$mongo = WPCW_MongoDB::get_instance();
$mongo->sync_to_mongodb();
```

### 3. Exportación de Datos

Formatos soportados:
- JSON
- CSV
- XML

```php
// Ejemplo de exportación
$mongo = WPCW_MongoDB::get_instance();
$data = $mongo->export_data('json', 'canjes');
```

## Estructura de Datos en MongoDB

### Colección: canjes
```json
{
    "_id": ObjectId(),
    "numero_canje": "String",
    "cliente_id": "Number",
    "cupon_id": "Number",
    "comercio_id": "Number",
    "fecha_solicitud_canje": "Date",
    "fecha_confirmacion_canje": "Date",
    "estado_canje": "String",
    "token_confirmacion": "String",
    "codigo_cupon_wc": "String",
    "id_pedido_wc": "Number"
}
```

## Solución de Problemas

### 1. Error de Conexión

Si aparece el mensaje "Error al conectar con MongoDB":
1. Verificar que MongoDB está ejecutándose
2. Comprobar la URI de conexión
3. Verificar credenciales
4. Revisar firewall y puertos

### 2. Errores de Sincronización

Si los datos no se sincronizan:
1. Verificar que la sincronización automática está activada
2. Comprobar permisos de escritura en MongoDB
3. Revisar los logs de WordPress

### 3. Problemas de Rendimiento

Si la sincronización es lenta:
1. Optimizar índices en MongoDB
2. Verificar la conexión de red
3. Ajustar el tamaño de lote de sincronización

## Registro de Cambios

### v1.0.0 - Integración Inicial MongoDB
- Implementación de conexión básica
- Sincronización automática de canjes
- Exportación de datos

### v1.1.0 - Mejoras de Rendimiento
- Optimización de sincronización
- Índices automáticos
- Mejora en manejo de errores

## Desarrollo y Mantenimiento

### Añadir Nuevos Campos a la Sincronización

1. Modificar la clase WPCW_MongoDB:
```php
public function sync_to_mongodb() {
    // Añadir nuevos campos aquí
    $data = array(
        'nuevo_campo' => $valor
    );
}
```

2. Actualizar índices si es necesario:
```php
$collection->createIndex(['nuevo_campo' => 1]);
```

### Pruebas

1. Verificar conexión:
```php
$mongo = WPCW_MongoDB::get_instance();
$connected = $mongo->test_connection();
```

2. Probar sincronización:
```php
$result = $mongo->sync_to_mongodb();
if ($result) {
    echo "Sincronización exitosa";
}
```

## Seguridad

### Mejores Prácticas

1. Usar autenticación siempre
2. Limitar permisos en MongoDB
3. Encriptar datos sensibles
4. Mantener copias de seguridad

### Configuración de Backup

1. Backup automático de MongoDB:
```bash
mongodump --uri="mongodb://localhost:27017/database" --out=/backup/
```

2. Restauración:
```bash
mongorestore --uri="mongodb://localhost:27017/database" /backup/
```

## Soporte

Para reportar problemas o solicitar ayuda:
1. Abrir un issue en GitHub
2. Contactar al soporte técnico
3. Consultar la documentación en línea

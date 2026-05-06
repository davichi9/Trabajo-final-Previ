# Trabajo-final-Previ

Sistema de gestión para servicio de alteraciones de prendas.

## Requisitos previos

- PHP 8.2+
- Composer
- MySQL 8.0+ (ejecutándose localmente o en XAMPP)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/davichi9/Trabajo-final-Previ.git
cd Trabajo-final-Previ
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar la base de datos

#### Opción A: Usando XAMPP

Si estás usando XAMPP, asegúrate de que MySQL esté ejecutándose.

1. Las credenciales están configuradas en `.env`:
   ```
   DATABASE_URL="mysql://root:@127.0.0.1:3306/previ?serverVersion=8.0&charset=utf8mb4"
   ```

2. Crear la base de datos (si no existe):
   ```bash
   & "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS previ CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. Ejecutar las migraciones:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
   Para insertar datos de prueba:
   ```
   php bin/console app:load-fixtures
   ```
   Datos de los usuarios:
   
| Nombre | Email | Contraseña | Rol |
|--------|-------|------------|-----|
| Carlos Hernández | carlos.hernandez@empresa.com | password123 | admin |
| Elena Fernández | elena.fernandez@empresa.com | password123 | supervisor |
| David Jiménez | david.jimenez@empresa.com | password123 | worker |


#### Opción B: Usando MySQL local

Si tienes MySQL instalado localmente, modifica `.env` con tus credenciales:
```
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/previ?serverVersion=8.0&charset=utf8mb4"
```

Luego ejecuta:
```bash
php bin/console doctrine:migrations:migrate
```

### 4. Iniciar el servidor

```bash
symfony serve
```

El servidor estará disponible en `http://127.0.0.1:8000`

## Base de datos

### Tablas disponibles

- **Clientes**: Información de clientes (nombre, apellidos, teléfono, email, domicilio)
- **Trabajadores**: Personal del negocio (nombre, apellidos, teléfono, email, contraseña, rol)
- **Pedidos**: Órdenes de clientes (estado, contenido, fechas, precio, pagado, cliente)
- **Prendas**: Catálogo de prendas (nombre, precio)

### Estados de pedidos

- `no terminado`: Pedido en progreso
- `terminado`: Pedido completado, listo para recoger
- `recogido`: Pedido entregado al cliente

### Trabajadores (Credenciales de prueba)


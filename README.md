# 💼 Sistema de Ventas Paul

Sistema de ventas completo desarrollado con **Laravel 12**, orientado a negocios que requieren emisión de comprobantes electrónicos, control de ventas, gestión multiusuario y múltiples empresas (RUCs).

---

## 🧠 Características Principales

- ✔️ Facturación electrónica vía [**NubeFact**](https://nubefact.com)
- ✔️ Panel multiusuario con roles y permisos (Spatie)
- ✔️ Soporte **multi-RUC** (multiempresa)
- ✔️ Interfaz moderna con **Jetstream + Livewire**
- ✔️ Gestión de productos, clientes y ventas
- ✔️ Punto de venta dinámico (POS) con **jQuery** y **DataTables**
- ✔️ Broadcasting en tiempo real con **Laravel Echo** + **Reverb**
- ✔️ Control de acceso basado en roles y permisos
- ✔️ Compatible con Laragon (Windows)

---

## 🛠️ Tecnologías Usadas

| Tecnología         | Rol                                                   |
|--------------------|--------------------------------------------------------|
| Laravel 12         | Backend Framework principal                            |
| Jetstream + Livewire | Gestión de sesiones y componentes interactivos      |
| Spatie Permission  | Control de roles y permisos                            |
| Laravel Echo       | Cliente JS para eventos en tiempo real                 |
| Laravel Reverb     | Servidor WebSocket oficial de Laravel (vía Pusher)     |
| jQuery             | Utilidades del frontend                                |
| DataTables         | Visualización de tablas (ventas, productos, etc.)      |
| NubeFact API       | Envío de facturas electrónicas                         |
| Laragon            | Servidor local (Windows) para desarrollo               |

---

## 🚀 Instalación Local (Windows + Laragon)

### ✅ Requisitos previos

- Laragon 6 o superior instalado 
- Composer
- Node.js + NPM (para instalar las dependencia)
- Git
- php  php-8.2.27-Win32-vs16-x64
- apache httpd-2.4.63-250122-win64-VS17

### 1. Clona el repositorio

```bash
git clone https://github.com/tu_usuario/sistema-ventas-paul.git
cd sistema-ventas-paul
```

### 2. Configura el entorno

```bash
cp .env.example .env
```

Edita `.env` y configura:

- Base de datos (MySQL o MariaDB de Laragon)
- NUBEFACT_API_KEY y URL
- REVERB_HOST / PORT si usas broadcasting

### 3. Instala dependencias PHP y JS

```bash
composer install
npm install
```

### 4. Ejecuta migraciones y seeders (opcional)

```bash
php artisan migrate --seed
```

Esto crea las tablas e inserta roles, permisos y usuarios base.

### 5. Compila los assets Para producción:
 generalmente los envio en el repositorio compilado, pero puede volver a compilar por si hay algun cambio en css o configuracion de reverb

```bash
npm run build 
```

### 6. Inicia el servidor local


O usa Laragon → "Start All" y accede a:

```
http://svp.test
```

(Recuerda añadir el dominio en `hosts` si usas Laragon personalizado)

---
### 8. Acceso desde otras pc en red local

 poner al final de :
 C:\laragon\bin\apache\httpd-2.4.63-250122-win64-VS17\conf\httpd.conf
 
 ```bash
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/svp/public"
    ServerName 192.168.1.20
    <Directory "C:/laragon/www/svp/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
 ```

## 🔊 Broadcasting con Laravel Echo + Reverb

Si usas Reverb (WebSockets):

0. Inicia el servidor Reverb:
 - he creado un .vbs para que pueda ser ejectudado en el arranque de windows
 - este .vbs ejecuta un script para inicilizar reverb.

Copia este .vbs o un acceso directo en 
```bash
windows + r
ejecuta shell:startup
```

 - tien un retraso de 10 segundo, para esperar que laragon se inicie automaticamente con windows
 - puedes cambiar este retraso para evitar que se ejecute el scrip antes que laragon este iniciado



contenido de iniciar-reverb.bat:
```bash
@echo off

timeout /t 30 /nobreak > NUL

@echo off
cd /d C:\laragon\www\svp
php artisan reverb:start

```

1. Inicia el servidor Reverb: (opcional, inicio manual)

```bash
php artisan reverb:start
```

2. Verifica que el archivo `.env` tenga:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=laravel
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

3. Laravel Echo está integrado con estas variables.

---

## 🔐 Acceso Inicial

**Usuario admin por defecto (si ejecutaste seed):**

```
Email: admin@gmail.com
Contraseña: admin
```

---

## ✨ Próximas mejoras sugeridas

- Integración con pasarela de pagos (Yape / Plin)
- Reportes PDF personalizados
- Cierre de caja automático
- Optimización de POS para pantallas táctiles

---

## 🧾 Licencia

Este sistema es de uso privado para el equipo de **Paul Herrera** .  
Si deseas una versión personalizada, contáctame.

---

## 📬 Contacto

¿Tienes dudas o sugerencias?

- Correo: PedroHerreraAqp@gmail.com.com
- telefono: 974783812
- GitHub: [@ozoneekiz](https://github.com/ozoneekiz)
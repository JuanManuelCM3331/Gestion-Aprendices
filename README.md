# Gestion Aprendices

Sistema integral para la gestión de aprendices, instructores, observaciones y evaluación de riesgos en un contexto educativo. Este proyecto forma parte de un trabajo de ciberseguridad y DevOps.

## Descripción General

**Gestion Aprendices** es una aplicación web desarrollada en PHP que permite:
- Gestionar usuarios del sistema con diferentes roles
- Administrar aprendices y su información
- Registrar instructores y asignar especialidades
- Crear observaciones y seguimiento de aprendices
- Evaluar riesgos con un sistema de clasificación
- Mantener un registro de auditoría de todas las acciones

## Requisitos

- **PHP:** >= 7.4 (Actualmente usa PHP 8.2)
- **Base de Datos:** MySQL >= 5.7 o MariaDB >= 10.2
- **Servidor Web:** Apache, Nginx, etc.
- **Docker:** (Opcional, para containerización)
- **Composer:** (Opcional, para dependencias)

## Instalación

### Con Docker Compose

1) Clonar el repositorio
```bash
git clone https://github.com/JuanManuelCM3331/New-Gestion-Aprendices.git
cd New-Gestion-Aprendices
```


2) Crear el archivo de variables de entorno

En la raíz del proyecto crea un archivo .env
Agrega al menos estas variables:
 con:
 ```
 DB_ROOT_PASSWORD=secretroot
DB_NAME=gestion_aprendices
DB_USER=admin
DB_PASS=admin123
```

3) Verificar que Docker esté instalado y funcionando

En Windows: asegúrate de tener Docker Desktop iniciado.
Desde PowerShell:
```
docker --version
docker compose version
```

4) Levantar los contenedores
```bash
docker compose up -d
docker compose ps
```

El contenedor db debe estar healthy o running

abrir: http://localhost:8080
Iniciar sesión en la web

el README menciona credenciales por defecto:
admin / admin123


## Credenciales por Defecto

- **Usuario:** admin
- **Contraseña:** admin123

**Nota de Seguridad:** Cambiar las credenciales por defecto inmediatamente después de la instalación.

## Estructura del Proyecto

```
New-Gestion-Aprendices/
├── App/
│   ├── index.php
│   └── Modules/
│       ├── Dashboard/
│       │   ├── login.php              # Sistema de autenticación
│       │   ├── dashboard.php          # Panel principal
│       │   ├── db.php                 # Configuración de BD
│       │   ├── crud.php               # Operaciones de BD
│       │   ├── auditoria.php          # Log de auditoría
│       │   ├── gestionUsuarios.php    # Gestión de usuarios
│       │   ├── gestionAprendices.php  # Gestión de aprendices
│       │   ├── gestionInstructores.php # Gestión de instructores
│       │   ├── gestionRiesgos.php     # Gestión de riesgos
│       │   └── observaciones.php      # Gestión de observaciones
│       └── ValoradorRiesgo/
│           └── calcularRiesgo.php     # Lógica de cálculo de riesgos
├── Dockerfile                         # Configuración Docker
├── docker-compose.yml                 # Orquestación de servicios
├── .gitignore
└── readme.md
```

## Características Principales

### Panel Administrador
- ✅ Gestión de usuarios (crear, ver, actualizar, eliminar)
- ✅ Gestión de aprendices y su información
- ✅ Gestión de instructores y especialidades
- ✅ Gestión de riesgos con clasificación
- ✅ Visualización del log de auditoria
- ✅ Control total del sistema

### Panel Instructor
- ✅ Ver listado de aprendices asignados
- ✅ Crear observaciones para aprendices
- ✅ Consultar información de riesgos

### Panel Aprendiz
- ✅ Ver observaciones asignadas
- ✅ Consultar información de riesgos asociados

## Modelo de Base de Datos

### Tabla `usuarios`
- `id` (PK) - Identificador único
- `username` - Nombre de usuario
- `password` - Contraseña hasheada
- `role` - Rol del usuario (admin, instructor, aprendiz)

### Tabla `aprendices`
- `id` (PK)
- `nombre` - Nombre del aprendiz
- `programa` - Programa educativo
- `usuario_id` (FK) - Referencia a usuarios

### Tabla `instructores`
- `id` (PK)
- `nombre` - Nombre del instructor
- `especialidad` - Área de especialización

### Tabla `riesgos`
- `id` (PK)
- `descripcion` - Descripción del riesgo
- `nivel` - Nivel de severidad
- `justificacion` - Justificación del riesgo

### Tabla `observaciones`
- `id` (PK)
- `aprendiz_id` (FK) - Referencia a aprendices
- `texto` - Contenido de la observación
- `autor_id` (FK) - Referencia a usuarios (autor)

### Tabla `auditoria`
- `id` (PK)
- `usuario` - Usuario que realiza la acción
- `accion` - Descripción de la acción
- `fecha` - Timestamp de la acción

## Características de Seguridad

- ✅ Hashing de contraseñas con `PASSWORD_DEFAULT`
- ✅ Consultas preparadas para prevenir SQL injection
- ✅ Autenticación basada en sesiones
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Sistema completo de auditoría
- ✅ Validación de entrada en formularios
- ✅ Protección contra CSRF mediante tokens de sesión

## Docker

### Configuración Docker Compose

El proyecto incluye configuración para ejecutarse con Docker:

```yaml
services:
  app:           # Servicio PHP 8.2 con Apache
  db:            # MySQL 8.0
```

**Variables de entorno (.env):**
- `DB_ROOT_PASSWORD` - Contraseña root de MySQL
- `DB_NAME` - Nombre de la base de datos
- `DB_USER` - Usuario de MySQL
- `DB_PASS` - Contraseña del usuario de BD

## Uso

1. **Iniciar sesión** con las credenciales por defecto
2. **Cambiar contraseña** recomendado en primer acceso
3. **Navegar** según el rol asignado
4. **Crear registros** de aprendices, instructores y observaciones
5. **Consultar auditoría** para seguimiento de actividades

## Flujo de Riesgos

1. El sistema calcula automáticamente el nivel de riesgo
2. Los riesgos se clasifican por nivel de severidad
3. Las observaciones se vinculan a evaluaciones de riesgo
4. Todo queda registrado en auditoría

## Desarrollo

### Estructura de módulos
- Cada módulo es independiente dentro de Dashboard
- Los ficheros de gestión (gestion*.php) manejan la interfaz
- CRUD centralizado en crud.php
- Configuración de BD en db.php

### Extensión del sistema
Para añadir nuevas funcionalidades:
1. Crear nuevo archivo en `App/Modules/Dashboard/`
2. Agregar funciones CRUD en `crud.php`
3. Actualizar logs de auditoría
4. Documentar cambios

## ¿Cómo la aplicación realizada reduce la deuda técnica?

En Gestión Aprendices la deuda técnica se reduce desde la misma forma en que está organizado el proyecto. Por ejemplo, cada módulo (usuarios, aprendices, instructores, riesgos, observaciones) está separado dentro de Dashboard y no todo revuelto en un solo archivo, entonces si después hay que corregir algo de "gestionRiesgos" no toca tocar el resto del sistema y arriesgar a romper otras partes. Además, el CRUD está centralizado en un solo archivo (crud.php), o sea que las operaciones contra la base de datos no están repetidas por todos lados; si algo cambia en la lógica de guardar o actualizar datos, se corrige en un solo lugar y no en diez archivos distintos, que es justo lo que genera deuda cuando un proyecto crece.
También ayuda que la configuración de la base de datos esté aparte en db.php y se maneje por variables de entorno (.env) en vez de quedar quemada en el código; así si el día de mañana cambia el usuario, la contraseña o el nombre de la base de datos, no hay que ir a buscar esos datos por todo el proyecto. Lo mismo pasa con Docker y docker-compose: como todo el entorno (PHP, Apache, MySQL) queda definido en archivos de configuración, cualquiera puede levantar el proyecto igual en su máquina sin tener que instalar y configurar todo a mano, lo que evita esos problemas típicos de "en mi PC sí funciona" que después se acumulan como deuda.
Por el lado de seguridad, usar contraseñas hasheadas, consultas preparadas para evitar inyección SQL, control de acceso por roles (RBAC) y protección CSRF, evita que esas vulnerabilidades queden pendientes para corregir después, que es una de las formas más comunes de deuda técnica en un sistema. Y el módulo de auditoría le da observabilidad al sistema, porque queda un registro de quién hizo qué y cuándo, entonces si algo falla se puede detectar a tiempo en vez de dejar que el error se acumule sin que nadie se dé cuenta. En resumen, la combinación de una estructura modular, configuración separada del código, uso de Docker y buenas prácticas de seguridad y auditoría es lo que hace que este proyecto no vaya acumulando "parches" desordenados con el tiempo.

## Soporte

Para reportar problemas, sugerencias o preguntas, por favor crea un [issue](https://github.com/JuanManuelCM3331/New-Gestion-Aprendices/issues) en el repositorio.

## Autor

**Juan Manuel Cardona Molina**
- Proyecto de Ciberseguridad y DevOps
- [Perfil GitHub](https://github.com/JuanManuelCM3331)

## Licencia

Para más información, contactar con el autor.

---

**Última actualización:** Junio 03, 2026

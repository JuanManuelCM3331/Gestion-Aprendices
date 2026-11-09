# Changelog — Historial de Cambios y Commits

Todos los cambios notables e historial de commits de **SGA — Sistema de Gestión y Seguimiento de Aprendices (`New-Gestion-Aprendices`)** están documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el proyecto sigue [Versionado Semántico (SemVer)](https://semver.org/lang/es/).

---

## [2.0.1] - 2026-11-09 — Pulido de UI/UX, Micro-interacciones, Docker Plug-and-Play y Gobernanza

### Added (Agregado)
- **Micro-interacciones y Animaciones Globales (`resources/views/`)**:
  - Animación de entrada progresiva (`fadeUp` + escalonado) en vistas y secciones.
  - Desplazamiento suave (`scroll-behavior: smooth`) con compensación de cabecera en los menús laterales.
  - Botones táctiles con elevación en `hover` y compresión al hacer clic (`active: scale(0.97)`).
  - Acordeones de edición y creación (`<details>`) con rotación de indicador `▾` y despliegue animado (`slideDown`).
  - Notificaciones flash auto-descartables con botón de cierre manual.
- **Contenerización Plug-and-Play (`Dockerfile` y `docker-compose.yml`)**:
  - Script de arranque automático en contenedor que instala dependencias de Composer, genera `.env` y `APP_KEY` si no existen tras clonar desde GitHub, y ejecuta migraciones y semillas idempotentes (`DatabaseSeeder`).
- **Documentación Legal y Trazabilidad**:
  - Incorporación de licencia libre **GNU General Public License v3.0** (`LICENSE`).
  - Incorporación de este registro histórico de versiones y commits (`CHANGELOG.md`).

### Fixed (Corregido)
- **`9c43147`** *(2026-11-09 — juanma3331)*: `enhanced(frontend): mejoras en el diseño visual y reduccion de fatiga visual` — Simplificación de tarjetas, eliminación de sobrecarga de etiquetas, restauración de paleta oscura (`slate-950` / `slate-900` / `indigo`) y corrección del scroll independiente en la barra lateral de navegación (`sidebar-scroll`).

---

## [2.0.0] - 2026-11-09 — Refactorización Integral a Laravel 12 (MVC + Arquitectura en Capas + Tailwind CSS)

### Changed (Cambiado / Refactorizado)
- **`9c443db`** *(2026-11-09 — juanma3331)*: `refactor(all): refactorizacion completa del proyecto y migracion a laravel`
  - **Migración de PHP Procedural a Laravel 12**: Eliminación completa del directorio monolítico legado `App/` y adopción de arquitectura limpia **MVC + Capas (Servicios de Dominio y Patrón Repositorio)**.
  - **Capa de Repositorios (`app/Repositories/`)**: 7 contratos (`Contracts/`) y 7 implementaciones Eloquent (`Eloquent/`) inyectadas mediante `AppServiceProvider`.
  - **Capa de Servicios (`app/Services/`)**:
    - `RiskEngineService`: Motor predictivo de riesgo (`desercion`, `academico`, `psicosocial`) con cálculo automático de semáforo (`verde`, `amarillo`, `rojo`) y mitigación automática al aprobar soportes/excusas.
    - `BienestarService`: Ajuste dinámico de pesos del modelo predictivo sin tocar el código fuente, bloqueo de cierre/eliminación de casos no resueltos y exportación masiva CSV de vulnerabilidad.
    - `InstructorService`: Registro ágil individual y masivo por ficha de observaciones, inasistencias y llamados de atención, más escalamiento en 1 clic a Bienestar.
    - `AdminGovernanceService`: Bitácora de auditoría estrictamente inmutable (`AuditLog`) con encadenamiento criptográfico **SHA-256** (`previous_hash` + `integrity_hash`), control granular de permisos por módulo y mantenimiento de tablas maestras (Sedes, Periodos Académicos, Programas y Fichas).
    - `AprendizPortalService`: Visibilidad transparente del semáforo de riesgo, historial cronológico de llamados de atención y felicitaciones, y carga de soportes/excusas médicas vinculadas a observaciones.
  - **Frontend 100% Tailwind CSS (`resources/views/`)**: Rediseño completo de todas las vistas sin Bootstrap.
  - **Limpieza de estructura**: Eliminación de archivos y carpetas redundantes o no utilizados por la arquitectura, y suite de pruebas automatizadas PHPUnit (`tests/Feature/SgaArchitectureAndRolesTest.php`).

### Added (Agregado previo a la migración en el mismo ciclo)
- **`18d29f6`** *(2026-11-09 — juanma3331)*: `feat(login): implementacion de autocompletado para perfiles de prueba en el login` — Tarjetas interactivas de acceso rápido para probar los 4 roles del sistema en un clic.

---

## [1.3.0] - 2026-06-10 a 2026-06-24 — Automatización CI/CD con GitHub Actions y Documentación

### Added (Agregado)
- **`d2c0dc7`** *(2026-06-10 — juanma3331)*: `feat(pipeline-devops.yml): agregar pipeline con jobs de validacion y entrega simulada`
- **`a04d01a`** *(2026-06-10 — juanma3331)*: `feat(artifact-demo.yml): agregar workflow para generacion y subida de artifact`
- **`3212ce0`** *(2026-06-10 — juanma3331)*: `feat(docker-build.yml): agregar workflow para construccion de imagen Docker`
- **`8e2966d`** *(2026-06-10 — juanma3331)*: `feat(ci-validacion.yml): agregar workflow de validacion de estructura del proyecto`
- **`d082aa7`** *(2026-06-10 — juanma3331)*: `feat(ci-basica.yml): agregar workflow basico de GitHub Actions`

### Fixed & Docs (Corregido y Documentación)
- **`3446f52`** *(2026-06-24 — Maria Paula Herrera Rivas)*: `Update README.md`
- **`192f608`** *(2026-06-10 — juanma3331)*: `fix(workflows): corregir nombres de archivo y directorio en verificaciones CI`

---

## [1.2.0] - 2026-05-28 a 2026-06-03 — Contenerización Docker y Landing Page Pública

### Added (Agregado)
- **`04d77dd`** *(2026-06-02 — juanma3331)*: `feat(landing.php): agregacion de una landing`
- **`4006202`** *(2026-06-02 — juanma3331)*: `feat(docker-compose.yml): agregacion de red docker personalizada`
- **`ae2c9c4`** *(2026-05-28 — juanma3331)*: `feat(.gitignore): integracion de secretos`
- **`4e9fa41`** *(2026-05-28 — juanma3331)*: `feat(Dockerfile): integracion con docker`

### Fixed & Docs (Corregido y Documentación)
- **`0cf9446`** *(2026-06-03 — Juan M. Cardona M.)*: `Update README.md`
- **`befc434`** *(2026-06-03 — juanma3331)*: `fix(docker): arreglo de error de ejecucion del docker`
- **`0ccf9dc`** *(2026-06-03 — Juan M. Cardona M.)*: `Revise README for installation and requirements clarity`
- **`fc8d8f2`** *(2026-06-02 — juanma3331)*: `fix(landing.php): arreglo de errores en el diseño visual`
- **`7bebda3`** *(2026-05-28 — Juan M. Cardona M.)*: `Refactor README.md for better structure and clarity`
- **`1b4b613`** *(2026-05-28 — Juan M. Cardona M.)*: `Update README with current project information and Docker support`
- **`10d4346`** *(2026-05-28 — juanma3331)*: `fix(App): creacion del archivo raiz, rutas relativas y correcciones varias`

---

## [1.1.0] - 2026-05-20 a 2026-05-27 — Modularización Inicial, Motor de Riesgo y CRUD

### Changed (Refactorizado y Mejorado)
- **`62dc5b3`** *(2026-05-27 — juanma3331)*: `refactor(Modules): refactorizacion de algunos componentes del diseño visual`
- **`e12c75c`** *(2026-05-23 — juanma3331)*: `refactor(dashboard): separacion de formularios`
- **`f0371b7`** *(2026-05-22 — juanma3331)*: `refactor(dashboard.php): refactorizacion del diseño visual de la web`
- **`e2cd8af`** *(2026-05-22 — juanma3331)*: `enhanced(dashboard): mejora en el diseño visual`
- **`fdaabd8`** *(2026-05-21 — juanma3331)*: `enhanced(dashboard.php): mejora en el diseño visual`
- **`c16956f`** *(2026-05-20 — juanma3331)*: `refactor: reorganizar estructura del proyecto y separar módulos`

### Fixed & Merges (Correcciones e Integraciones)
- **`08d6724`** *(2026-05-23 — juanma3331)*: `fix(dashboard): rol admin puede crear observacion`
- **`d7245ac`** *(2026-05-23 — juanma3331)*: `docs(dashboard): agregacion de comentarios en el archivo`
- **`cfdd9da`** *(2026-05-21 — juanma3331)*: `Merge branch 'arreglos-y-mejoras'`
- **`119c34d`** *(2026-05-21 — juanma3331)*: `fix(db.php): correcion en la conexion de la base de datos`
- **`eb2be73`** *(2026-05-21 — Juan M. Cardona M.)*: `Merge pull request #4 from JuanManuelCM3331/arreglos-y-mejoras`
- **`2633544`** *(2026-05-21 — Juan M. Cardona M.)*: `Merge branch 'main' into arreglos-y-mejoras`
- **`17c2027`** *(2026-05-21 — Juan M. Cardona M.)*: `Resolver conflictos: Mantener versión final mejorada con todas las optimizaciones`
- **`710a547`** *(2026-05-21 — juanma3331)*: `fix(crud.php): arreglo en la creacion de usuarios y roles`
- **`4e5359b`** *(2026-05-21 — juanma3331)*: `fix(crud.php): arreglo en la creacion de usuarios y asignacion de roles`
- **`d56b391`** *(2026-05-21 — Juan M. Cardona M.)*: `Merge pull request #3 from JuanManuelCM3331/refactorizacion-de-archivos`
- **`7b2fab7`** *(2026-05-20 — Juan M. Cardona M.)*: `Merge pull request #2 from JuanManuelCM3331/refactorizacion-de-archivos`
- **`0c3aead`** *(2026-05-20 — juanma3331)*: `fix(calcularRiesgo.php): arreglo de invocaciones.`
- **`7833d1f`** *(2026-05-20 — Juan M. Cardona M.)*: `Merge pull request #1 from JuanManuelCM3331/refactorizacion-de-archivos`

---

## [1.0.0] - 2026-05-19 — Lanzamiento Inicial del Sistema

### Added (Agregado)
- **`590eae0`** *(2026-05-19 — juanma3331)*: `feat(all): primera subida del sistema` — Versión inicial de Gestión de Aprendices con roles, observaciones y cálculo de riesgo básico.
- **`3296263`** *(2026-05-19 — juanma3331)*: `enhanced(README.md): mejora en el estilo visual del readme`
- **`7e39da4`** *(2026-05-19 — Juan M. Cardona M.)*: `Fix formatting in readme.md for roles and classification`

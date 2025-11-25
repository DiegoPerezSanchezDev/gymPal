# 📋 Registro de tareas y tablas pendientes para **GymPal**

## ✅ Tareas pendientes (orden de prioridad)

### 1️⃣ Correcciones y pruebas inmediatas
- ✅ Corregir error Ziggy: asegurarse de que la ruta `progress.index` esté declarada en `routes/web.php` y ejecutar `php artisan ziggy:generate`.
- ✅ Probar la navegación al apartado **Progreso** desde el menú de escritorio y móvil.
- ✅ Quitar el botón **Cerrar Sesión** del menú flotante móvil y moverlo a la vista de perfil (enlace a `/login`).
- ✅ Limpiar advertencias de Tailwind `@apply` reemplazándolas por clases utilitarias o componentes.
- ✅ Verificar que la búsqueda de rutinas solo muestre contenidos de usuarios seguidos (lógica en `WorkoutController`).

### 2️⃣ Mejoras de UI/UX y diseño premium
- Rediseñar visualmente la pestaña **Popular** del Feed (gradientes, iconos, micro‑animaciones).
- Pulir la pestaña **Cerca** del Feed (mostrar badge de distancia).
- Mejorar la UI de **Rutinas Guardadas** (grid, filtros, estilos premium).
- Añadir meta‑tags SEO a las nuevas páginas (**Progreso**, **Historial**).
- Implementar skeleton loaders y micro‑animaciones en Feed, Historial y Descubrir.
- Implementar modo oscuro global con toggle y persistencia en la tabla `users` (campo `theme`).
- Añadir tipografía premium (Google Font **Inter** o **Outfit**) y mejorar contraste.

### 3️⃣ Funcionalidades de comunidad y gamificación
- **Historias de 24 h** (Stories) – tabla `stories`, barra superior en el feed, modal de visualización.
- **Logros / Badges** – tablas `badges` y `user_badge`, disparadores en eventos (post creado, rutina, entrenamiento).
- **Reacciones y comentarios en Stories** – tabla `story_reactions` y UI de emojis.
- **Compartir Rutina en Chat** – tipo `shared_workout` en `messages`, botón “Adjuntar Rutina”.
- **Notificaciones en tiempo real** – Badge y dropdown con Laravel Echo + Pusher (o WebSockets).
- **Exportar Rutina como PDF** – botón en vista de rutina (jsPDF / dompdf).
- **Filtros avanzados en Calendario** – dropdowns en `CalendarFilter.vue` y query adaptada.
- **Integración con Calendario externo** (Google/Apple) – exportar .ics o usar API.
- **API de ciudades y deportes** – endpoints para autocompletar en formularios.
- **Registro rápido y completo** – mejorar formulario de alta (validación en tiempo real, UX fluida).
- **Login mejorado** – pantalla atractiva, recuperación de contraseña, validación.
- **Quitar logs opcionales** – eliminar logs de depuración o hacerlos configurables.

### 4️⃣ Calidad de código, pruebas y CI/CD
- Escribir tests unitarios (PHPUnit, Jest/Vitest) para los nuevos filtros y lógica de progreso.
- Tests de integración / e2e (Cypress o Playwright) que cubran flujos completos.
- Configurar linting y formateo (ESLint, Prettier, PHP CS Fixer) con husky pre‑commit.
- Añadir GitHub Actions: build, test, lint, deploy a staging.
- Implementar monitoreo de errores (Sentry o Bugsnag) en Laravel y Vue.
- Generar sitemap XML y robots.txt.
- Añadir datos estructurados (JSON‑LD) para rutinas y usuarios.

### 5️⃣ Optimización de rendimiento
- Lazy‑load de imágenes y videos (`loading="lazy"`).
- Code‑splitting de componentes Vue (`defineAsyncComponent`).
- Cache de respuestas API (Laravel Cache, encabezados `Cache‑Control`).
- Compresión y CDN para assets estáticos.
- Optimizar Tailwind con purge y generar CSS en modo producción.
- Pre‑conexión a fuentes externas (Google Fonts, iconos).

### 6️⃣ Accesibilidad (a11y)
- Añadir `aria‑label` y `role` a componentes interactivos.
- Garantizar contraste de colores (WCAG AA/AAA).
- Navegación completa por teclado (focus visible, `tabindex`).
- Link “Saltar al contenido” en layout.
- Test de accesibilidad con Lighthouse/axe.

### 7️⃣ Internacionalización (i18n)
- Soporte multilingüe con `vue-i18n` y archivos `lang` de Laravel.
- Formato de fechas y números según locale (`Intl.DateTimeFormat`).

---

## 📂 Tablas nuevas a crear (9 tablas)

| Nº | Tabla | Campos principales | Comentario |
|---|-------|-------------------|------------|
| 1️⃣ | **stories** | `id`, `user_id`, `content`, `image_path`, `expires_at`, `created_at`, `updated_at` | Publicaciones temporales de 24 h. |
| 2️⃣ | **badges** | `id`, `name`, `description`, `icon_path`, `created_at`, `updated_at` | Definición de logros/gamificación. |
| 3️⃣ | **user_badge** | `id`, `user_id`, `badge_id`, `earned_at` | Relación many‑to‑many entre usuarios y logros. |
| 4️⃣ | **story_reactions** | `id`, `user_id`, `story_id`, `emoji`, `created_at` | Reacciones (emoji) a las Stories. |
| 5️⃣ | **cities** | `id`, `name`, `country_code`, `created_at`, `updated_at` | Lista de ciudades para autocompletar en formularios. |
| 6️⃣ | **sports** | `id`, `name`, `icon_path`, `created_at`, `updated_at` | Lista de deportes para autocompletar. |
| 7️⃣ | **workout_shares** *(opcional, si no se usa `shared_workout` en `messages`)* | `id`, `message_id`, `workout_id`, `created_at` | Relación entre mensaje y rutina compartida. |
| 8️⃣ | **notifications** *(ya existe, pero se ampliará)* | `id`, `user_id`, `type`, `data (json)`, `read_at`, `created_at` | Extender para notificaciones en tiempo real. |
| 9️⃣ | **api_keys** *(para integraciones externas como Google Calendar)* | `id`, `user_id`, `service`, `key`, `expires_at`, `created_at` | Almacenar claves de APIs externas. |

### Detalle por bloque

#### Bloque 1 – Historias y reacciones
- `stories` y `story_reactions` permiten crear, visualizar y reaccionar a contenido efímero de 24 h.
- Necesitan relaciones **User ↔ Story** (uno‑a‑muchos) y **Story ↔ Reaction** (uno‑a‑muchos).

#### Bloque 2 – Gamificación
- `badges` y `user_badge` gestionan los logros y su asignación a los usuarios.
- Cada vez que se cumpla una condición (ej. “primer post”) se insertará un registro en `user_badge`.

#### Bloque 3 – Datos auxiliares
- `cities` y `sports` sirven como fuentes de datos para autocompletar en formularios de perfil, búsqueda y creación de rutinas.

#### Bloque 4 – Compartir rutinas y notificaciones
- `workout_shares` (si se prefiere) enlaza un mensaje con la rutina que se comparte.
- `notifications` se ampliará para soportar eventos en tiempo real (likes, comentarios, nuevas stories, etc.).
- `api_keys` guardará tokens externos (p.ej. Google Calendar) para integraciones.

---

## 📅 Próximos pasos recomendados
1. Generar las migraciones para las 9 tablas (una por tabla). 
2. Crear los modelos Eloquent correspondientes y definir sus relaciones. 
3. Ejecutar `php artisan migrate` y verificar que las tablas aparecen en la base de datos. 
4. Implementar los endpoints API básicos (CRUD) para `stories`, `badges`, `cities` y `sports`. 
5. Continuar con las tareas de UI/UX y pruebas según la prioridad listada arriba.

---

*Este documento queda como referencia central para el equipo. Cada punto puede desglosarse en tickets de Jira/Linear según convenga.*

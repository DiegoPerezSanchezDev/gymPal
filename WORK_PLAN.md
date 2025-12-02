# 🚀 Plan de Mejoras - Sesión Actual

## ✅ Completado
- [x] Autocompletado de ciudades con Geoapify en Discover

---

## 📋 Tareas Pendientes (En Orden)

### 1. 🤝 Mejorar Página de Conexiones (30-40 min)
**Prioridad: ALTA**

#### Subtareas:
- [ ] Añadir sistema de tabs (Todos / Pendientes / Aceptados)
- [ ] Implementar paginación (máx 12 conexiones por página)
- [ ] Añadir contador de conexiones por tab
- [ ] Mejorar diseño de las tarjetas de conexión
- [ ] Añadir estados de carga (skeletons)
- [ ] Botón de "Cargar más" en lugar de scroll infinito

**Archivos a modificar:**
- `app/Http/Controllers/ConnectionController.php`
- `resources/js/Pages/Connections/Index.vue`

---

### 2. 💾 Sistema de Guardados Mejorado (25-30 min)
**Prioridad: ALTA**

#### Subtareas:
- [ ] Permitir guardar posts (además de rutinas)
- [ ] Crear página unificada de "Guardados"
- [ ] Tabs para "Rutinas" y "Posts"
- [ ] Añadir fecha de guardado
- [ ] Botón de "Quitar de guardados"
- [ ] Contador de items guardados

**Archivos a crear/modificar:**
- `app/Models/SavedPost.php` (nuevo)
- `app/Http/Controllers/SavedItemsController.php` (nuevo)
- `resources/js/Pages/Saved/Index.vue` (nuevo)
- Migración para tabla `saved_posts`

---

### 3. 📊 Estadísticas Rápidas en el Perfil (20-25 min)
**Prioridad: MEDIA**

#### Subtareas:
- [ ] Widget de "Estadísticas del Mes"
  - Entrenamientos completados
  - Días activos
  - Rutinas creadas
- [ ] Racha de días consecutivos
- [ ] Gráfica simple de actividad (últimos 7 días)
- [ ] Badges de logros básicos

**Archivos a modificar:**
- `app/Http/Controllers/ProfileController.php`
- `resources/js/Pages/Profile/ShowPublic.vue`
- Crear componente `StatsWidget.vue`

---

### 4. ✨ Mejoras Visuales Rápidas (15-20 min)
**Prioridad: BAJA (pero impacto visual alto)**

#### Subtareas:
- [ ] Animaciones de entrada en tarjetas (fade-in, slide-up)
- [ ] Hover effects mejorados
- [ ] Loading states con skeletons en todas las páginas
- [ ] Transiciones suaves entre páginas
- [ ] Micro-animaciones en botones
- [ ] Efecto de "like" animado en posts
- [ ] Progress bars animados

**Archivos a modificar:**
- `resources/css/app.css` (añadir animaciones globales)
- Componentes individuales (PostCard, WorkoutCard, etc.)

---

## 🎯 Orden de Ejecución Recomendado

1. **Primero**: Conexiones (es funcional y muy visible)
2. **Segundo**: Guardados (complementa bien las conexiones)
3. **Tercero**: Estadísticas (añade valor al perfil)
4. **Cuarto**: Mejoras visuales (el toque final)

---

## ⏱️ Tiempo Total Estimado
**2 horas aproximadamente** para completar todo

---

## 💡 Notas
- Cada tarea es independiente, se puede hacer por separado
- Haremos commits después de cada feature completada
- Si algo no gusta, revertimos y seguimos con la siguiente

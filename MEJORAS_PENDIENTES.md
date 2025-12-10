# 📋 Lista de Mejoras Pendientes para GymPal

## 🔴 PRIORIDAD ALTA

### 1. Completar Modal de Conexiones
**Descripción**: Añadir listas completas de Seguidores y Siguiendo al modal
**Tareas**:
- [ ] Enviar lista de `followers` desde backend (ProfileController)
- [ ] Enviar lista de `following` desde backend (ProfileController)
- [ ] Actualizar modal para mostrar listas de Seguidores
- [ ] Actualizar modal para mostrar lista de Siguiendo
- [ ] Añadir avatares y links a perfiles en todas las listas
**Estimación**: 30-45 minutos
**Archivo**: `app/Http/Controllers/ProfileController.php`, `resources/js/Pages/Profile/ShowPublic.vue`

### 2. Duplicados en Modal de Compartir
**Problema**: El modal de compartir posts muestra usuarios duplicados
**Causa**: El método `getGymPalsAttribute()` en `User.php` hace merge de conexiones enviadas y recibidas sin eliminar duplicados
**Solución**: Añadir `->unique('id')` después del merge
**Archivo**: `app/Models/User.php` línea ~145
```php
return $sentAndAccepted->merge($receivedAndAccepted)->unique('id');
```

### 3. Estadísticas en el Perfil
**Descripción**: Widget con métricas clave del usuario
**Componentes**:
- Contador de entrenamientos completados
- Días activos este mes
- Racha de actividad (días consecutivos)
- Gráfica simple de progreso (Chart.js ya está instalado)
- Total de rutinas creadas
- Total de posts publicados

## 🟡 PRIORIDAD MEDIA

### 4. Historias de 24h (Stories) 📸
**Descripción**: Sistema completo de historias temporales estilo Instagram/WhatsApp
**Tareas**:
- [ ] Crear tabla `stories` (user_id, content, image_path, expires_at)
- [ ] Crear tabla `story_reactions` (user_id, story_id, emoji)
- [ ] Modelo Story con relaciones
- [ ] Barra superior en Feed con círculos de historias
- [ ] Modal de visualización de historias (swipe, timer)
- [ ] Subir imagen/video para historia
- [ ] Auto-eliminación después de 24h (job scheduled)
- [ ] Reacciones con emojis
- [ ] Contador de visualizaciones
- [ ] UI responsive y animada
**Estimación**: 8-10 horas
**Prioridad**: ⭐⭐⭐⭐⭐ (Feature muy solicitada)

### 5. Sistema de Logros / Badges 🏆
**Descripción**: Gamificación con logros desbloqueables
**Tareas**:
- [ ] Crear tabla `badges` (name, description, icon_path)
- [ ] Crear tabla `user_badge` (user_id, badge_id, earned_at)
- [ ] Definir logros iniciales (primer post, 10 entrenamientos, etc.)
- [ ] Eventos que disparan logros
- [ ] UI para mostrar badges en perfil
- [ ] Notificación al desbloquear logro
- [ ] Página de logros disponibles
**Estimación**: 6-8 horas

### 6. Agrupar Notificaciones por Tipo
**Descripción**: Organizar notificaciones en grupos expandibles
**Tareas**:
- [ ] Diseñar UI para notificaciones agrupadas
- [ ] Implementar lógica de agrupación en backend
- [ ] Actualizar frontend para mostrar grupos
- [ ] Añadir opción de expandir/colapsar grupos
- [ ] Añadir contador por tipo de notificación
**Estimación**: 2-3 horas
**Archivo**: `app/Http/Controllers/NotificationController.php`, `resources/js/Pages/Notifications/Index.vue`

### 7. Mejoras de UX
**Áreas a mejorar**:
- [ ] Añadir animaciones de transición en modal de conexiones
- [ ] Mejorar feedback visual al aceptar/rechazar solicitudes
- [ ] Añadir toast notifications para acciones de conexión
- [ ] Mejorar estados de carga (skeletons)
- [ ] Animaciones de entrada/salida en modales
- [ ] Transiciones suaves entre páginas
- [ ] Hover effects mejorados en cards
- [ ] Micro-interacciones (botones, likes, etc.)
- [ ] Animación al guardar/desguardar
- [ ] Feedback visual al compartir

### 8. Optimizaciones de Performance
**Tareas**:
- [ ] Revisar queries N+1 en conexiones
- [ ] Añadir índices en tabla connections si es necesario
- [ ] Optimizar carga de avatares (lazy loading)
- [ ] Implementar caché para contadores de conexiones
- [ ] Prefetch de datos en navegación
- [ ] Code-splitting de componentes Vue
- [ ] Lazy-load de imágenes y videos

## 🟢 PRIORIDAD BAJA

### 9. Compartir Rutina en Chat 💬
**Descripción**: Enviar rutinas directamente en mensajes
**Tareas**:
- [ ] Añadir tipo `shared_workout` en messages
- [ ] Botón "Adjuntar Rutina" en chat
- [ ] Preview de rutina en mensaje
- [ ] Click para ver rutina completa
**Estimación**: 3-4 horas

### 10. Notificaciones en Tiempo Real 🔔
**Descripción**: Push notifications con Laravel Echo + Pusher
**Tareas**:
- [ ] Configurar Laravel Echo y Pusher
- [ ] Eventos de notificación en tiempo real
- [ ] Badge con contador en navbar
- [ ] Dropdown de notificaciones
- [ ] Sonido/vibración al recibir
**Estimación**: 4-6 horas

### 11. Exportar Rutina como PDF 📄
**Descripción**: Descargar rutina en formato PDF
**Tareas**:
- [ ] Integrar jsPDF o dompdf
- [ ] Diseño del PDF (logo, formato)
- [ ] Botón de descarga en vista de rutina
- [ ] Incluir ejercicios, series, descansos
**Estimación**: 2-3 horas

### 12. Integración con Calendario Externo 📅
**Descripción**: Exportar entrenamientos a Google Calendar / Apple Calendar
**Tareas**:
- [ ] Crear tabla `api_keys` para tokens
- [ ] Generar archivos .ics
- [ ] Integración con Google Calendar API
- [ ] Botón "Añadir a Calendario"
**Estimación**: 6-8 horas

### 13. Mejoras Visuales Generales
**Áreas a mejorar**:
- Consistencia de colores en dark mode
- Optimizar espaciados en mobile
- Añadir más micro-animaciones
- Mejorar accesibilidad (ARIA labels)
- Tipografía premium (Google Font Inter o Outfit)

### 14. Documentación
**Tareas**:
- [ ] Documentar lógica de conexiones en código
- [ ] Crear diagrama de estados de conexión
- [ ] Documentar API de notificaciones
- [ ] Actualizar README con nuevas features

---

## ✅ COMPLETADO RECIENTEMENTE

### Sistema de Guardados Unificado
**Estado**: ✅ COMPLETADO
- ✅ Guardar rutinas funciona
- ✅ Guardar posts implementado
- ✅ Página unificada de guardados existe (`Workouts/Saved.vue`)
- ✅ Botón de guardar en PostCard con notificaciones toast
- ✅ Dark mode completo
- ✅ Enlace en navegación principal

### Sistema de Conexiones Refinado
**Estado**: ✅ COMPLETADO
- ✅ Sistema de conexiones con estados (pending/accepted)
- ✅ Diferenciación entre GymPals, Seguidores y Siguiendo
- ✅ Auto-aceptación de solicitudes mutuas
- ✅ Contadores en perfil público
- ✅ Modal clickeable para GymPals
- ✅ Diseño mobile-first en página de conexiones

### Scroll Infinito en Notificaciones
**Estado**: ✅ COMPLETADO
- ✅ Cursor-based pagination en backend
- ✅ Carga automática al hacer scroll
- ✅ Indicador de carga visual
- ✅ Sin botones de paginación

---

## 📊 MÉTRICAS DE ÉXITO

- Tiempo de carga < 2s (producción)
- 0 errores en consola
- Todas las features funcionan en móvil
- Navegación fluida (60fps)
- Feedback visual en todas las acciones

---

**Última actualización**: 2025-12-05
**Rama actual**: `master`

# 📋 Lista de Mejoras Pendientes para GymPal

## 🔴 PRIORIDAD ALTA

### 1. 📊 Página de Estadísticas Diferenciadora ⭐⭐⭐⭐⭐
**Descripción**: Crear una página dedicada de estadísticas que sea única y diferenciadora en el mercado
**Features a implementar**:
- [ ] 🔥 **Heatmap de Actividad** - Calendario visual estilo GitHub mostrando días activos
- [ ] 📈 **Gráficas de Progreso** - Peso levantado, volumen total, frecuencia de entrenamientos
- [ ] 🏆 **Sistema de Logros y Badges** - Gamificación con badges desbloqueables
- [ ] 📊 **Comparativas con GymPals** - Ver cómo te comparas con tus amigos
- [ ] 💪 **Récords Personales (PRs)** - Tracking de mejores marcas por ejercicio
- [ ] 📅 **Racha de Entrenamientos** - Visualización motivacional de días consecutivos
- [ ] 🎯 **Objetivos y Metas** - Sistema de tracking de progreso hacia objetivos
- [ ] 📉 **Análisis de Tendencias** - Gráficas de mejora semanal/mensual
- [ ] 🌟 **Nivel y Experiencia** - Sistema de niveles basado en actividad
- [ ] 🔔 **Insights Personalizados** - Recomendaciones basadas en datos

**Tecnologías**:
- Chart.js (ya instalado) para gráficas
- Componente Heatmap personalizado
- Animaciones con Framer Motion o CSS

**Estimación**: 8-12 horas
**Prioridad**: ⭐⭐⭐⭐⭐ (MÁXIMA - Feature diferenciadora)

### 2. Duplicados en Modal de Compartir
**Problema**: El modal de compartir posts muestra usuarios duplicados
**Solución**: Ya tiene `->unique('id')` aplicado
**Estado**: ✅ COMPLETADO

### 3. Estadísticas en el Perfil
**Estado**: ⚠️ PARCIALMENTE COMPLETADO
- ✅ Componente ProfileStats creado
- ✅ Backend calcula estadísticas
- ⏸️ Pendiente: Integrar en página dedicada (ver tarea #1)

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
**Prioridad**: ⭐⭐⭐⭐

### 5. Sistema de Logros / Badges 🏆
**Descripción**: Gamificación con logros desbloqueables (integrado con Stats)
**Tareas**:
- [ ] Crear tabla `badges` (name, description, icon_path)
- [ ] Crear tabla `user_badge` (user_id, badge_id, earned_at)
- [ ] Definir logros iniciales (primer post, 10 entrenamientos, etc.)
- [ ] Eventos que disparan logros
- [ ] UI para mostrar badges en perfil y stats
- [ ] Notificación al desbloquear logro
- [ ] Página de logros disponibles
**Estimación**: 6-8 horas
**Nota**: Parte de esto se integrará en la página de estadísticas

### 6. Agrupar Notificaciones por Tipo
**Descripción**: Organizar notificaciones en grupos expandibles
**Tareas**:
- [ ] Diseñar UI para notificaciones agrupadas
- [ ] Implementar lógica de agrupación en backend
- [ ] Actualizar frontend para mostrar grupos
- [ ] Añadir opción de expandir/colapsar grupos
- [ ] Añadir contador por tipo de notificación
**Estimación**: 2-3 horas

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
- ✅ Modal clickeable para GymPals, Seguidores y Siguiendo
- ✅ Listas completas con avatares y links
- ✅ Diseño mobile-first en página de conexiones
- ✅ Fix de fotos de perfil en modales

### Scroll Infinito en Notificaciones
**Estado**: ✅ COMPLETADO
- ✅ Cursor-based pagination en backend
- ✅ Carga automática al hacer scroll
- ✅ Indicador de carga visual
- ✅ Sin botones de paginación

### Componente de Estadísticas
**Estado**: ✅ COMPLETADO
- ✅ ProfileStats component creado
- ✅ Backend calcula estadísticas (workouts, racha, nivel, etc.)
- ✅ Diseño responsive con gradientes
- ✅ Dark mode completo

---

## 📊 MÉTRICAS DE ÉXITO

- Tiempo de carga < 2s (producción)
- 0 errores en consola
- Todas las features funcionan en móvil
- Navegación fluida (60fps)
- Feedback visual en todas las acciones

---

## 🎯 PRÓXIMOS PASOS RECOMENDADOS

1. **Crear Página de Estadísticas** (8-12h) ← PRIORIDAD #1
2. **Implementar Heatmap de Actividad** (parte de #1)
3. **Sistema de Badges** (6-8h) - Integrar con stats
4. **Historias 24h** (8-10h) - Feature muy solicitada

---

**Última actualización**: 2025-12-10
**Rama actual**: `feature/quick-fixes`
**Próxima feature**: Página de Estadísticas Diferenciadora

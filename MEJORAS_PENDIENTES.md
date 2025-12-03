# 📋 Lista de Mejoras Pendientes para GymPal

## 🔴 BUGS A ARREGLAR

### 1. Duplicados en Modal de Compartir
**Problema**: El modal de compartir posts muestra usuarios duplicados
**Causa**: El método `getGymPalsAttribute()` en `User.php` hace merge de conexiones enviadas y recibidas sin eliminar duplicados
**Solución**: Añadir `->unique('id')` después del merge
**Archivo**: `app/Models/User.php` línea ~145
```php
return $sentAndAccepted->merge($receivedAndAccepted)->unique('id');
```

---

## ✨ FUNCIONALIDADES NUEVAS

### 2. Sistema de Guardados Unificado
**Estado**: Parcialmente implementado
- ✅ Guardar rutinas funciona (tabla `saved_workouts`, método `toggleSave` en WorkoutController)
- ❌ Guardar posts NO implementado
- ❌ Página unificada de guardados NO existe

**Tareas**:
1. Crear tabla `saved_posts` (similar a `saved_workouts`)
2. Crear modelo `SavedPost`
3. Añadir método `toggleSavePost` en PostController
4. Añadir relación `savedPosts()` en modelo User
5. Crear página `Saved/Index.vue` con tabs (Rutinas/Posts)
6. Añadir botón "Guardar" en PostCard
7. Mover botón "Compartir" al header del post (junto a los 3 puntos)

### 3. Estadísticas en el Perfil
**Descripción**: Widget con métricas clave del usuario
**Componentes**:
- Contador de entrenamientos completados
- Días activos este mes
- Racha de actividad (días consecutivos)
- Gráfica simple de progreso (Chart.js ya está instalado)
- Total de rutinas creadas
- Total de posts publicados

### 4. Mejoras Visuales Generales
**Áreas a mejorar**:
- Animaciones de entrada/salida en modales
- Transiciones suaves entre páginas
- Loading states más pulidos (skeletons)
- Hover effects mejorados en cards
- Micro-interacciones (botones, likes, etc.)
- Animación al guardar/desguardar
- Feedback visual al compartir

---

## 🎨 MEJORAS DE UX/UI

### 5. Optimización de Carga
- ✅ Prefetch implementado
- ❌ Lazy loading de imágenes
- ❌ Infinite scroll en feed
- ❌ Optimización de queries (N+1)

### 6. Notificaciones en Tiempo Real
- ❌ WebSockets/Pusher para notificaciones live
- ❌ Sonido/vibración en nuevas notificaciones
- ❌ Badge count en tiempo real

### 7. Chat Mejorado
- ❌ Indicador de "escribiendo..."
- ❌ Mensajes de voz
- ❌ Compartir ubicación del gym
- ❌ Reacciones a mensajes
- ❌ Mensajes temporales/efímeros

### 8. Rutinas y Entrenamientos
- ❌ Duplicar rutina de otro usuario
- ❌ Plantillas de rutinas por deporte
- ❌ Temporizador integrado para entrenamientos
- ❌ Modo "En vivo" para entrenar con la rutina
- ❌ Comparar progreso con amigos

### 9. Social Features
- ❌ Reels/Stories de entrenamientos
- ❌ Challenges/Retos entre amigos
- ❌ Leaderboards semanales
- ❌ Badges y logros
- ❌ Grupos/Comunidades por deporte

### 10. Descubrimiento
- ❌ Filtros avanzados (edad, género, nivel)
- ❌ Búsqueda por gimnasio
- ❌ "Cerca de mí" con mapa
- ❌ Sugerencias basadas en IA

---

## 🔧 MEJORAS TÉCNICAS

### 11. Performance
- ❌ Caché de queries frecuentes (Redis)
- ❌ CDN para assets estáticos
- ❌ Compresión de imágenes automática
- ❌ Service Worker para PWA

### 12. SEO y Accesibilidad
- ❌ Meta tags dinámicos
- ❌ Open Graph para compartir
- ❌ Accesibilidad ARIA
- ❌ Modo alto contraste

### 13. Seguridad
- ❌ Rate limiting en APIs
- ❌ 2FA (autenticación de dos factores)
- ❌ Reportes de usuarios/contenido
- ❌ Moderación de contenido

---

## 📱 MOBILE APP

### 14. PWA Features
- ❌ Instalable como app
- ❌ Notificaciones push
- ❌ Funciona offline (básico)
- ❌ Sincronización en background

### 15. Native Features
- ❌ Acceso a cámara optimizado
- ❌ Geolocalización precisa
- ❌ Integración con Health/Fitness apps
- ❌ Compartir a redes sociales nativo

---

## 🎯 PRIORIDADES INMEDIATAS (Esta Sesión)

1. **🔴 CRÍTICO**: Arreglar duplicados en modal de compartir
2. **🟡 IMPORTANTE**: Implementar guardado de posts completo
3. **🟢 MEJORA**: Añadir estadísticas básicas al perfil
4. **🟢 MEJORA**: Pulir animaciones y transiciones

---

## 📊 MÉTRICAS DE ÉXITO

- Tiempo de carga < 2s (producción)
- 0 errores en consola
- Todas las features funcionan en móvil
- Navegación fluida (60fps)
- Feedback visual en todas las acciones

---

**Última actualización**: 2025-12-03
**Rama actual**: `feature/ux-improvements`

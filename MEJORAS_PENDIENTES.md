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
### 2. Sistema de Guardados Unificado
**Estado**: ✅ COMPLETADO
- ✅ Guardar rutinas funciona
- ✅ Guardar posts implementado
- ✅ Página unificada de guardados existe (`Workouts/Saved.vue`)
- ✅ Botón de guardar en PostCard con notificaciones toast
- ✅ Dark mode completo
- ✅ Enlace en navegación principal

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

## 🎯 PRIORIDADES INMEDIATAS (Esta Sesión)

1. **🔴 CRÍTICO**: Arreglar duplicados en modal de compartir
2. **✅ COMPLETADO**: Implementar guardado de posts completo
3. **🟡 IMPORTANTE**: Añadir estadísticas básicas al perfil
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

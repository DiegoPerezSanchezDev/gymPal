# 📋 TAREAS PENDIENTES - GymPal

## 🔴 PRIORIDAD ALTA

### 1. Completar Listas de Conexiones en Modal
- [ ] Enviar lista de `followers` desde backend (ProfileController)
- [ ] Enviar lista de `following` desde backend (ProfileController)
- [ ] Actualizar modal para mostrar listas de Seguidores y Siguiendo
- [ ] Añadir avatares y links a perfiles en las listas

### 2. Testing de Lógica de Conexiones
- [ ] Probar flujo completo de enviar solicitud
- [ ] Probar auto-aceptación cuando ambos se envían solicitud
- [ ] Probar aceptar/rechazar solicitudes manualmente
- [ ] Verificar que estados se actualizan correctamente en UI
- [ ] Probar desconectar y reconectar

### 3. Verificar Notificaciones
- [ ] Probar scroll infinito en notificaciones
- [ ] Verificar que carga correctamente al hacer scroll
- [ ] Probar filtros (Todas/No leídas)
- [ ] Verificar que notificaciones de conexión funcionan

## 🟡 PRIORIDAD MEDIA

### 4. Optimizaciones de Performance
- [ ] Revisar queries N+1 en conexiones
- [ ] Añadir índices en tabla connections si es necesario
- [ ] Optimizar carga de avatares (lazy loading)
- [ ] Implementar caché para contadores de conexiones

### 5. Mejoras de UX
- [ ] Añadir animaciones de transición en modal de conexiones
- [ ] Mejorar feedback visual al aceptar/rechazar solicitudes
- [ ] Añadir toast notifications para acciones de conexión
- [ ] Mejorar estados de carga (skeletons)

### 6. Funcionalidades de Posts Guardados
- [ ] Verificar que posts guardados se muestran correctamente
- [ ] Probar guardar/desguardar posts
- [ ] Verificar notificaciones de posts guardados
- [ ] Revisar página de guardados (posts + rutinas)

## 🟢 PRIORIDAD BAJA

### 7. Agrupar Notificaciones por Tipo
- [ ] Diseñar UI para notificaciones agrupadas
- [ ] Implementar lógica de agrupación en backend
- [ ] Actualizar frontend para mostrar grupos
- [ ] Añadir opción de expandir/colapsar grupos

### 8. Mejoras Visuales
- [ ] Revisar consistencia de colores en dark mode
- [ ] Optimizar espaciados en mobile
- [ ] Añadir más micro-animaciones
- [ ] Mejorar accesibilidad (ARIA labels)

### 9. Documentación
- [ ] Documentar lógica de conexiones en código
- [ ] Crear diagrama de estados de conexión
- [ ] Documentar API de notificaciones
- [ ] Actualizar README con nuevas features

## 🐛 BUGS CONOCIDOS

- Ninguno reportado actualmente

## 📝 NOTAS

### Cambios Recientes Completados ✅
- Sistema de conexiones refinado (GymPals/Seguidores/Siguiendo)
- Scroll infinito en notificaciones
- Modal de conexiones clickeable
- Contadores en perfil público
- Auto-aceptación de solicitudes mutuas
- Icono de guardar rutina en dorado
- Diseño mobile-first en página de conexiones

### Próximos Pasos Sugeridos
1. Completar listas de Seguidores/Siguiendo en modal
2. Testing exhaustivo del flujo de conexiones
3. Optimizaciones de performance
4. Agrupar notificaciones por tipo

---
**Última actualización**: 2025-12-05

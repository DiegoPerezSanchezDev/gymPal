📋 ESTADO ACTUAL DE TAREAS (Actualizado 18/01/2026)

✅ FINALIZADO HOY (UI Polish & Modals)
------------------------------------------
🎨 Feed y Contenido
✅ GPS Icon: Rediseñado con estilo premium (fondo indigo, animación pulse, más destacado).
✅ "Te sigue" Badge: Implementado en Discover, perfiles públicos y listas de conexiones.

🏋️‍♂️ Rutinas
✅ Selectores de Series: Reemplazados `<select>` por `SetTypeDropdown` premium con colores e iconos.
✅ Identidad de Series: Las filas de series ahora tienen colores (Caranja para Warmup, Rojo para Fallo, Púrpura para Drop Set).
✅ Lógica Drop Set: Añadido icono ⬇️ y sangría visual para drops.
✅ Confirmación Borrado: Implementado `ConfirmModal` para eliminar ejercicios.
✅ Botones Cancelar/Guardar: Ahora tienen ancho fijo en desktop (no se estiran).

🏢 Gimnasios
✅ GymController Fix: Corregido error 500 al cargar detalles de gimnasios.
✅ GymMembersModal: Integrado en `ShowPublic.vue` y `Discover.vue` para ver todos los asistentes.
✅ "Te sigue" en Gyms: Añadido flag is_following_me en respuestas de gimnasios.

⏳ PENDIENTE (Próximos Pasos)
-----------------------------
🏢 Gimnasios
- [ ] Limpieza DB: Verificar si hay datos de gimnasios falsos para eliminar.

🗺️ Discover
- [x] ~~Buscador de Compañero (Modo Oscuro): Mejorado contraste y coloreadas esferas.~~

---

✅ COMPLETADO ANTERIORMENTE (Blindaje y Estabilidad)
------------------------------------------
🐛 Bugs Críticos
✅ SQL Error (BigInt): Corregido el fallo al filtrar por categorías como "Crossfit" en PostgreSQL.
✅ Live Workout: Blindado el error "Undefined array key name" al iniciar entrenamientos con datos corruptos.
✅ Notificación Guardado: Se eliminó el tick verde redundante al guardar posts.
✅ Calendario Historial: Filtro corregido para mostrar solo el día seleccionado.

🗺️ Discover y Mapa
✅ Error 0.0 km: Corregido. Ahora muestra "Muy cerca" e ignora coordenadas 0,0 en el backend.
✅ Flujo GPS Premium: Modal explicativo solo si falta ubicación + memoria de intención (Mapa/Lista).
✅ Skeletons: Implementados en todos los cambios de filtros y búsquedas.

🏋️‍♂️ Rutinas y UI
✅ Colores Pública/Privada: Cambiados a Esmeralda/Rose para total claridad.
✅ Mejora Modo Dark: Etiquetas con mejor contraste y visibilidad.
✅ Navegación Inteligente: El botón "Atrás" te devuelve al Chat si vienes de allí.
✅ SelectInput Fix: Ahora muestra "Seleccionar..." correctamente si no hay valor.
✅ Filtros Neutros: Botones "Todas" y ordenamiento en Slate (gris) para no saturar.

# 🚀 Sistema de Prefetch Implementado

## ¿Qué hace?

El sistema de prefetch precarga las páginas antes de que el usuario haga clic, haciendo que la navegación se sienta **instantánea**.

## Estrategias Implementadas

### 1. **Auto-Prefetch (Automático)**
- Se activa 2 segundos después de cargar cualquier página
- Precarga automáticamente las rutas más importantes:
  - `/feed` - Feed principal
  - `/discover` - Descubrir usuarios
  - `/connections` - Conexiones
  - `/chat` - Chat
  - `/workouts` - Rutinas

- Las precargas están espaciadas 500ms entre sí para no saturar la red
- Se ejecuta en segundo plano sin afectar el rendimiento

### 2. **Prefetch on Hover (Al pasar el mouse)**
- Cuando el usuario pasa el mouse sobre un enlace de navegación
- Espera 100ms antes de precargar (evita precargas accidentales)
- Si el usuario sale del enlace antes de 100ms, cancela la precarga
- Implementado en:
  - Feed
  - Descubrir
  - Conexiones

## Beneficios

✅ **Navegación instantánea**: Las páginas ya están cargadas cuando haces clic
✅ **Uso inteligente de recursos**: Solo precarga lo importante
✅ **No afecta el rendimiento**: Se ejecuta en segundo plano
✅ **Cancelable**: Si el usuario cambia de opinión, no desperdicia recursos

## Cómo Funciona Técnicamente

1. **Auto-Prefetch**: Se ejecuta automáticamente en `AuthenticatedLayout`
2. **Hover-Prefetch**: Se añade con `v-on="prefetchOnHover('/ruta')"` en los enlaces
3. **Composable reutilizable**: `usePrefetch()` puede usarse en cualquier componente

## Ejemplo de Uso

```vue
<script setup>
import { usePrefetch } from '@/composables/usePrefetch';

const { prefetchOnHover } = usePrefetch();
</script>

<template>
  <Link 
    href="/mi-ruta" 
    v-on="prefetchOnHover('/mi-ruta')"
  >
    Mi Enlace
  </Link>
</template>
```

## Notas

- El prefetch usa la API nativa de Inertia.js
- Solo funciona con rutas de Inertia (no enlaces externos)
- Los errores de prefetch se capturan y no afectan la experiencia del usuario

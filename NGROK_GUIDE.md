# 📱 Guía para Ver en Móvil con Ngrok

## 🚀 Pasos Rápidos

### 1. Lanzar Ngrok
```powershell
.\start_share.bat
```

### 2. Copiar la URL
- Se abrirá una ventana con la URL de Ngrok
- Ejemplo: `https://abc123.ngrok-free.app`
- **COPIA ESTA URL**

### 3. Actualizar .env
Abre el archivo `.env` y cambia:
```
APP_URL=http://localhost:8081
```
Por:
```
APP_URL=https://TU-URL-DE-NGROK.ngrok-free.app
```

### 4. Limpiar caché
```powershell
php artisan config:clear
```

### 5. Abrir en el móvil
- Abre la URL de Ngrok en el navegador del móvil
- ¡Listo! Ya puedes probar la app

---

## 🔄 Para Volver a Desarrollo Local

### 1. Cerrar Ngrok
- Presiona `Ctrl + C` en la ventana de Ngrok

### 2. Restaurar .env
```
APP_URL=http://localhost:8081
```

### 3. Limpiar caché
```powershell
php artisan config:clear
```

---

## ✨ Nuevas Funciones para Probar

### Conexiones Mejoradas
- **Tabs**: Todas / Pendientes / Aceptadas
- **Paginación**: Máximo 12 GymPals por página
- **Botón "Ver más"**: Cuando hay más conexiones

### Discover Mejorado
- **Autocompletado de ciudades**: Escribe 3 letras y aparecen sugerencias reales
- **Búsqueda por ciudad**: Encuentra usuarios por ubicación

---

## 💡 Consejos

- Mantén el PC encendido mientras usas Ngrok
- La URL de Ngrok cambia cada vez que reinicias
- Si algo no funciona, verifica que `APP_URL` esté bien configurado

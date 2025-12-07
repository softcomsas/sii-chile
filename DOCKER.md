# Guía de Docker - SII Chile

## 📦 Construcción de la Imagen

### Construcción Local

```bash
docker build -t sii-chile:latest .
```

### Construcción con Tag Específico

```bash
docker build -t sii-chile:1.0.0 .
```

## 🚀 Ejecución del Contenedor

### Ejecución con Variables de Entorno (Recomendado)

```bash
docker run -d \
  --name sii-chile-app \
  -p 8000:80 \
  -e DB_DSN="mysql:host=db;dbname=sii_chile" \
  -e DB_USER=root \
  -e DB_PASS=secret \
  -e JWT_SECRET=your-jwt-secret-key \
  -e SMTP_HOST=smtp.gmail.com \
  -e SMTP_USER=user@example.com \
  -e SMTP_PASS=password \
  -v ${PWD}/runtime:/app/runtime \
  -v ${PWD}/upload:/app/upload \
  sii-chile:latest
```

### Ejecución con Docker Compose (Recomendado)

Crea un archivo `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    image: sii-chile:latest
    ports:
      - "8000:80"
    volumes:
      # Directorios persistentes
      - ./runtime:/app/runtime
      - ./upload:/app/upload
      - ./web/assets:/app/web/assets
    environment:
      # Base de datos
      - DB_DSN=mysql:host=db;dbname=sii_chile
      - DB_USER=root
      - DB_PASS=${DB_PASSWORD}
      # JWT
      - JWT_SECRET=${JWT_SECRET}
      # SMTP (opcional)
      - SMTP_HOST=${SMTP_HOST:-smtp.gmail.com}
      - SMTP_USER=${SMTP_USER}
      - SMTP_PASS=${SMTP_PASS}
      - SMTP_PORT=${SMTP_PORT:-587}
      - SMTP_ENCRYPTION=${SMTP_ENCRYPTION:-tls}
    depends_on:
      - db
    restart: unless-stopped
  
  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_PASSWORD}
      - MYSQL_DATABASE=sii_chile
    volumes:
      - mysql-data:/var/lib/mysql
    restart: unless-stopped

volumes:
  mysql-data:
```

Crea un archivo `.env` con tus secretos:

```bash
DB_PASSWORD=your-secure-password
JWT_SECRET=your-super-secret-jwt-key
SMTP_USER=noreply@example.com
SMTP_PASS=your-smtp-password
```

Ejecutar:

```bash
docker compose up -d
```

## 🔧 Configuración con Variables de Entorno

### Variables Requeridas

Los archivos de configuración **están incluidos en la imagen** y leen variables de entorno.

#### Obligatorias:

| Variable | Descripción | Ejemplo |
|----------|-------------|----------|
| `DB_DSN` | DSN de conexión a la base de datos | `mysql:host=db;dbname=sii_chile` |
| `DB_USER` | Usuario de la base de datos | `root` |
| `DB_PASS` | Contraseña de la base de datos | `secret123` |
| `JWT_SECRET` | Clave secreta para JWT | `your-super-secret-key` |

#### Opcionales (con valores por defecto):

| Variable | Default | Descripción |
|----------|---------|-------------|
| `SMTP_HOST` | `smtp.gmail.com` | Servidor SMTP |
| `SMTP_USER` | `''` | Usuario SMTP |
| `SMTP_PASS` | `''` | Contraseña SMTP |
| `SMTP_PORT` | `587` | Puerto SMTP |
| `SMTP_ENCRYPTION` | `tls` | Tipo de encriptación (tls/ssl) |
| `MAIL_USE_FILE` | `false` | Enviar emails a archivos (desarrollo) |
| `ADMIN_EMAIL` | `admin@example.com` | Email del administrador |
| `SMS_USER` | `user` | Usuario servicio SMS |
| `SMS_PASS` | `pass` | Contraseña servicio SMS |

## 🧪 Pruebas

### 1. Verificar que el Contenedor está Corriendo

```bash
docker ps
```

Deberías ver algo como:
```
CONTAINER ID   IMAGE              STATUS         PORTS                  NAMES
abc123def456   sii-chile:latest   Up 2 minutes   0.0.0.0:8000->80/tcp   sii-chile-app
```

### 2. Verificar Logs

```bash
docker logs sii-chile-app
```

### 3. Probar la Aplicación

Abre tu navegador en:
```
http://localhost:8000
```

### 4. Verificar Health Check

```bash
docker inspect --format='{{json .State.Health}}' sii-chile-app
```

### 5. Ejecutar Comandos dentro del Contenedor

```bash
# Acceder al shell del contenedor
docker exec -it sii-chile-app bash

# Ejecutar comandos Yii
docker exec -it sii-chile-app php yii migrate

# Verificar configuración
docker exec -it sii-chile-app php yii
```

## 📊 Monitoreo

### Ver Recursos Utilizados

```bash
docker stats sii-chile-app
```

### Inspeccionar el Contenedor

```bash
docker inspect sii-chile-app
```

## 🛑 Detener y Limpiar

### Detener el Contenedor

```bash
docker stop sii-chile-app
```

### Eliminar el Contenedor

```bash
docker rm sii-chile-app
```

### Eliminar la Imagen

```bash
docker rmi sii-chile:latest
```

## 🐙 Usar Imagen desde GitHub Container Registry

### 1. Autenticarse en GHCR

```bash
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin
```

### 2. Descargar la Imagen

```bash
docker pull ghcr.io/softcomsas/sii-chile:latest
```

### 3. Ejecutar desde GHCR

```bash
docker run -d \
  --name sii-chile-app \
  -p 8000:80 \
  -e DB_DSN="mysql:host=db;dbname=sii_chile" \
  -e DB_USER=root \
  -e DB_PASS=secret \
  -e JWT_SECRET=your-jwt-secret-key \
  -v ${PWD}/runtime:/app/runtime \
  -v ${PWD}/upload:/app/upload \
  ghcr.io/softcomsas/sii-chile:latest
```

## 🔍 Troubleshooting

### El contenedor se detiene inmediatamente

Verifica los logs:
```bash
docker logs sii-chile-app
```

Causas comunes:
- Faltan variables de entorno requeridas (DB_DSN, DB_USER, DB_PASS, JWT_SECRET)
- Error de conexión a la base de datos

### Error de permisos en runtime/upload

```bash
# Ajustar permisos localmente
chmod -R 777 runtime upload web/assets
```

### No se puede conectar a la base de datos

Verifica que:
1. Las variables de entorno `DB_DSN`, `DB_USER`, `DB_PASS` estén correctamente configuradas
2. El host de la base de datos sea accesible desde el contenedor
3. Las credenciales sean correctas

```bash
# Verificar variables de entorno
docker exec sii-chile-app env | grep DB_

# Probar conexión desde el contenedor
docker exec -it sii-chile-app ping db
```

## 📝 Notas de Seguridad

- ✅ Los archivos de configuración usan `getenv()` y no contienen secretos hardcodeados
- ✅ Las credenciales se pasan como variables de entorno en runtime
- ✅ Los archivos `*-local.php` del directorio raíz están en `.gitignore` (no se commitean)
- ✅ La imagen no contiene credenciales hardcodeadas
- ⚠️ Usa secretos de Kubernetes o Docker Secrets en producción
- ⚠️ Los permisos 777 en `runtime/upload/web/assets` son para desarrollo; ajusta según necesidad en producción
- ⚠️ Protege tu archivo `.env` y no lo commitees al repositorio

## 🏗️ CI/CD

La imagen se construye automáticamente en GitHub Actions cuando:
- Se hace push a la rama `main`
- Se crea un tag con formato `v*.*.*`
- Se abre un Pull Request

Las imágenes están disponibles en:
```
ghcr.io/softcomsas/sii-chile:latest
ghcr.io/softcomsas/sii-chile:main
ghcr.io/softcomsas/sii-chile:v1.0.0
```

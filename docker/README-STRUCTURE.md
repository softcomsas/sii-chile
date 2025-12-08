# Estructura de Docker - SII Chile

Este directorio contiene todos los archivos relacionados con Docker que no son necesarios para el funcionamiento normal de la aplicación.

## 📁 Estructura

```
docker/
├── README.md              # Documentación completa de Docker
├── Makefile              # Comandos útiles (make build, make run, etc.)
├── docker-entrypoint.sh  # Script de inicio (ejecuta migraciones + Apache)
├── apache/
│   └── remoteip.conf     # Configuración para proxy inverso
└── examples/
    ├── nginx-proxy.conf           # Ejemplo de Nginx como reverse proxy
    └── docker-compose.proxy.yml   # Ejemplo con Traefik + MySQL
```

## 🚀 Inicio Rápido

```bash
# Desde la raíz del proyecto:

# Construir imagen
make -f docker/Makefile build

# Ejecutar contenedor de prueba
make -f docker/Makefile run

# Ver todos los comandos disponibles
make -f docker/Makefile help
```

## 📖 Documentación

Ver `README.md` en este directorio para la documentación completa.

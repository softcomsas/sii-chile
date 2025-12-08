# Makefile para gestión de Docker
# Usar: make <comando>

.PHONY: help build run stop clean logs shell test

# Variables
IMAGE_NAME = sii-chile
IMAGE_TAG = latest
CONTAINER_NAME = sii-api

help: ## Mostrar esta ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Construir la imagen Docker
	docker build -t $(IMAGE_NAME):$(IMAGE_TAG) .

build-no-cache: ## Construir la imagen sin usar caché
	docker build --no-cache -t $(IMAGE_NAME):$(IMAGE_TAG) .

run: ## Ejecutar el contenedor con variables de entorno mínimas
	docker run -d \
		--name $(CONTAINER_NAME) \
		-p 8080:80 \
		-e YII_ENV=prod \
		-e YII_DEBUG=0 \
		-e DB_DSN="mysql:host=host.docker.internal;dbname=sii_chile" \
		-e DB_USER=root \
		-e DB_PASS=secret \
		-e JWT_SECRET=change-this-secret-key \
		$(IMAGE_NAME):$(IMAGE_TAG)

run-compose: ## Ejecutar con docker-compose
	docker-compose up -d

stop: ## Detener el contenedor
	docker stop $(CONTAINER_NAME) || true

remove: stop ## Eliminar el contenedor
	docker rm $(CONTAINER_NAME) || true

clean: remove ## Limpiar contenedor e imagen
	docker rmi $(IMAGE_NAME):$(IMAGE_TAG) || true

logs: ## Ver logs del contenedor
	docker logs -f $(CONTAINER_NAME)

shell: ## Acceder al shell del contenedor
	docker exec -it $(CONTAINER_NAME) bash

test: ## Probar el contenedor (healthcheck)
	curl -f http://localhost:8080/ || (echo "❌ El contenedor no responde" && exit 1)
	@echo "✅ Contenedor respondiendo correctamente"

restart: stop run ## Reiniciar el contenedor

rebuild: stop remove build run ## Reconstruir y ejecutar

ps: ## Mostrar estado del contenedor
	docker ps -a --filter name=$(CONTAINER_NAME)

inspect: ## Inspeccionar el contenedor
	docker inspect $(CONTAINER_NAME)

push: ## Subir imagen a registry (requiere configuración)
	docker tag $(IMAGE_NAME):$(IMAGE_TAG) ghcr.io/softcomsas/$(IMAGE_NAME):$(IMAGE_TAG)
	docker push ghcr.io/softcomsas/$(IMAGE_NAME):$(IMAGE_TAG)

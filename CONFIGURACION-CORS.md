# Configuraciones para evitar Mixed Content (HTTPS/HTTP)

## Problema identificado:
- Frontend en HTTPS (https://sistema.ayalarepuestos.cl)
- API en HTTP (http://sistema.ayalarepuestos.cl)
- Navegador bloquea peticiones HTTP desde HTTPS (mixed content)

## Soluciones implementadas:

### 1. URLs relativas en frontend
En lugar de:
```javascript
fetch('http://sistema.ayalarepuestos.cl/sii-chile/api/folio/mantenedor/subir-caf')
```

Usar:
```javascript
const apiUrl = `${window.location.protocol}//${window.location.host}/sii-chile/api/folio/mantenedor/subir-caf`;
fetch(apiUrl)
```

### 2. Configuración automática de protocolo
```javascript
class ApiService {
    constructor() {
        this.baseUrl = `${window.location.protocol}//${window.location.host}/sii-chile/api`;
    }
}
```

### 3. URLs disponibles:

#### Desarrollo (HTTP):
- http://localhost/sii-chile/api/folio/mantenedor
- http://localhost/sii-chile/api/folio/mantenedor/subir-caf

#### Producción (HTTPS):
- https://sistema.ayalarepuestos.cl/sii-chile/api/folio/mantenedor
- https://sistema.ayalarepuestos.cl/sii-chile/api/folio/mantenedor/subir-caf

### 4. Headers necesarios:
```javascript
headers: {
    'Content-Type': 'application/json',
    'ambiente': 'PROD'  // o 'DEV' para desarrollo
}
```

### 5. Configuración para Angular:
```typescript
// environment.ts
export const environment = {
  production: false,
  apiUrl: `${window.location.protocol}//${window.location.host}/sii-chile/api`
};

// environment.prod.ts
export const environment = {
  production: true,
  apiUrl: `${window.location.protocol}//${window.location.host}/sii-chile/api`
};
```

### 6. Test de CORS actualizado:
- Archivo: test-cors-real.html
- Usa URLs relativas para evitar mixed content
- Detecta automáticamente el protocolo correcto

## Endpoints finales funcionando:

✅ GET /folio/mantenedor
✅ POST /folio/mantenedor/subir-caf  
✅ OPTIONS /folio/mantenedor/subir-caf
✅ GET /folio/mantenedor/select

## CORS configurado para:
- Origin: ['*'] (todos los dominios)
- Methods: GET, POST, PUT, DELETE, OPTIONS
- Headers: Content-Type, Authorization, ambiente
- Mixed content: Resuelto con URLs relativas

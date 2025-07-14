// Ejemplo de código para tu frontend Angular/JavaScript
// Este código detecta automáticamente el protocolo (HTTP/HTTPS)

class ApiService {
    constructor() {
        // Detectar automáticamente el protocolo y host
        this.baseUrl = `${window.location.protocol}//${window.location.host}/sii-chile/api`;
    }

    async subirCaf(data) {
        try {
            const response = await fetch(`${this.baseUrl}/folio/mantenedor/subir-caf`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'ambiente': 'PROD' // o 'DEV' según corresponda
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al subir CAF:', error);
            throw error;
        }
    }

    async getMantenedorFolio(params = {}) {
        try {
            const queryParams = new URLSearchParams(params);
            const response = await fetch(`${this.baseUrl}/folio/mantenedor?${queryParams}`, {
                method: 'GET',
                headers: {
                    'ambiente': 'PROD' // o 'DEV' según corresponda
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al obtener mantenedor folio:', error);
            throw error;
        }
    }
}

// Uso del servicio
const apiService = new ApiService();

// Ejemplo de uso para subir CAF
async function ejemploSubirCaf() {
    try {
        const result = await apiService.subirCaf({
            archivo_caf: 'contenido_del_archivo',
            rut_empresa: '12345678-9',
            codigo_documento: 33
        });
        
        console.log('CAF subido exitosamente:', result);
    } catch (error) {
        console.error('Error:', error);
    }
}

// Ejemplo de uso para obtener datos
async function ejemploObtenerDatos() {
    try {
        const result = await apiService.getMantenedorFolio({
            rut_empresa: '12345678-9'
        });
        
        console.log('Datos obtenidos:', result);
    } catch (error) {
        console.error('Error:', error);
    }
}

// Para Angular, puedes usar este servicio así:
/*
@Injectable({
  providedIn: 'root'
})
export class SiiApiService {
  private baseUrl: string;

  constructor(private http: HttpClient) {
    this.baseUrl = `${window.location.protocol}//${window.location.host}/sii-chile/api`;
  }

  subirCaf(data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/folio/mantenedor/subir-caf`, data, {
      headers: {
        'ambiente': 'PROD'
      }
    });
  }

  getMantenedorFolio(params?: any): Observable<any> {
    return this.http.get(`${this.baseUrl}/folio/mantenedor`, {
      params: params,
      headers: {
        'ambiente': 'PROD'
      }
    });
  }
}
*/

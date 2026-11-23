# Academia KRIAMOS - Fase 7

Sistema PHP para capacitación de empleados y contratistas de KRIAMOS.

## Alcance acumulado hasta Fase 7

### Fase 1
- Estructura base del proyecto.
- Conexión MySQL/MariaDB con PDO.
- Plantilla Bootstrap corporativa KRIAMOS.
- Registro de empleados y contratistas con foto.
- Diferenciación por sede: GAEE, GALM y Oficina.
- Diferenciación de empleados por equipo: Operaciones, Mantenimiento, Compras, Gestión Humana y Administrativos.

### Fase 2
- Administración de cursos.
- Creación de módulos.
- Carga de materiales: manuales, videos, podcast/audio, PDF, Word, PowerPoint e imágenes.
- Asignación por tipo de usuario, sede, equipo y cargo.

### Fase 3
- Banco de preguntas por módulo.
- Examen de 10 preguntas aleatorias.
- Cronómetro de 1 minuto por pregunta.
- Bloqueo de respuesta enviada.
- Nota mínima aprobatoria del 80%.
- Reintento con preguntas diferentes después de reprobar.

### Fase 4
- Certificados PDF.
- Código QR de verificación.
- Vigencia de seis meses.
- Historial de certificaciones.

### Fase 5
- Calendario de recertificación.
- Reactivación automática cada seis meses.
- Recordatorios por correo 5 días antes, 1 día antes y día de reactivación.
- Cola de notificaciones.

### Fase 6
- Dashboard gerencial.
- Meta de cumplimiento de empleados capacitados del 95%.
- Tasa de completitud por módulos asignados.
- Tasa de aprobación y promedio de calificación.
- Tiempo dedicado a capacitación.
- Cumplimiento por granja/sede y equipo.
- Adquisición de habilidades.
- Exportación CSV del tablero.

### Fase 7
- Sistema de recompensas solo para empleados.
- Regla 100%: 4 pollos congelados.
- Regla 90% a 99.99%: 1 pollo congelado.
- Contratistas no generan recompensas.
- Generación automática de recompensa al aprobar un intento con 90% o 100%.
- Panel administrativo de recompensas.
- Consulta de recompensas por cédula.
- Registro de estado: pendiente, entregada o anulada.
- Registro de responsable de entrega, fecha y observaciones.
- Indicadores de recompensas y pollos pendientes/entregados.
- Exportación CSV de recompensas.

## Instalación rápida

1. Crear la base de datos importando las migraciones en orden:

```bash
mysql -u usuario -p < database/migrations/001_create_people_table.sql
mysql -u usuario -p academia_kriamos < database/migrations/002_create_courses_modules_materials.sql
mysql -u usuario -p academia_kriamos < database/migrations/003_create_exam_bank_and_attempts.sql
mysql -u usuario -p academia_kriamos < database/migrations/004_create_certificates.sql
mysql -u usuario -p academia_kriamos < database/migrations/005_create_calendar_and_notifications.sql
mysql -u usuario -p academia_kriamos < database/migrations/006_create_dashboard_metrics.sql
mysql -u usuario -p academia_kriamos < database/migrations/007_create_rewards.sql
```

2. Copiar `.env.example` a `.env` si se usará configuración por variables de entorno.

3. Ajustar credenciales en:

```text
app/config/database.php
app/config/mail.php
app/config/rewards.php
```

4. Instalar dependencias para PDF y QR:

```bash
composer install
```

5. Apuntar el servidor web a la carpeta:

```text
public/
```

## Rutas principales de la Fase 7

```text
public/index.php?page=admin_recompensas
public/index.php?page=mis_recompensas
public/index.php?page=recompensas_export
```

## Nota técnica

La recompensa se genera automáticamente al consultar el resultado de un examen aprobado, siempre que el participante sea empleado y la calificación sea igual o superior al 90%. El campo `exam_attempt_id` tiene restricción única en la tabla `rewards`, evitando duplicados si el usuario abre varias veces el resultado del examen.

## Fase 9 - PWA y modo sin conexión

Esta fase agrega la base para que Academia KRIAMOS funcione como aplicación web progresiva, instalable desde navegadores móviles compatibles y con descarga local de materiales de estudio.

### Funcionalidades agregadas

- Archivo `manifest.json` para instalación como PWA.
- `service-worker.js` para cachear la estructura base de la aplicación.
- Página `offline.html` para navegación sin conexión.
- Botón de instalación cuando el navegador soporte la instalación de PWA.
- Consulta de materiales descargables por cédula.
- Descarga de materiales de cursos asignados usando Cache API del navegador.
- Registro en base de datos de materiales descargados por persona.
- Opción para limpiar materiales descargados del dispositivo.
- Banner de estado de conexión: online/offline.

### Migración requerida

Ejecutar después de las migraciones anteriores:

```bash
mysql -u usuario -p academia_kriamos < database/migrations/008_create_offline_downloads.sql
```

### Consideración operativa

El modo sin conexión está diseñado para estudiar materiales previamente descargados. Por seguridad, trazabilidad y control del cronómetro, el examen oficial debe presentarse con conexión al servidor.

### Prueba recomendada

1. Ingresar a `index.php?page=offline`.
2. Consultar una cédula registrada.
3. Descargar uno o varios materiales.
4. Activar modo avión o desconectar internet.
5. Abrir nuevamente la aplicación instalada o el navegador.
6. Confirmar que los materiales descargados puedan abrirse desde el caché local.

## Fase 9 - Chat IA con base de conocimiento interna

Esta fase agrega el módulo **Asistente KRIAMOS IA**, orientado a empleados y contratistas registrados en la planilla digital.

### Funcionalidades incluidas

- Chat por cédula, sin usuario ni contraseña para empleados/contratistas.
- Base de conocimiento interna administrable.
- Respuestas basadas en registros cargados por KRIAMOS: POE, manuales, cursos, app y FAQ.
- Trazabilidad de sesiones y mensajes.
- Panel administrativo para agregar, activar o inactivar conocimiento.
- Filtros por audiencia: todos, empleado o contratista.
- Filtros por sede: todos, GAEE, GALM u Oficina.
- Filtros por equipo: Operaciones, Mantenimiento, Compras, Gestión Humana o Administrativos.
- Mensaje de uso responsable para temas sanitarios críticos.
- Escalamiento sugerido cuando la pregunta menciona Influenza Aviar, Newcastle, Salmonella, mortalidad, medicamentos, eutanasia, cuarentena o emergencias.

### Migración requerida

Ejecutar después de la Fase 8:

```bash
mysql -u usuario -p academia_kriamos < database/migrations/009_create_ai_chat.sql
```

### Configuración

El archivo `app/config/ai.php` deja el sistema en modo recomendado inicial:

```php
'mode' => 'local_knowledge'
```

En este modo el asistente responde únicamente con la base de conocimiento interna. No consulta internet y no reemplaza decisiones técnicas del Médico Veterinario, Administrador de Granja, Dirección de Operaciones, SG-SST ni autoridad sanitaria competente.

### Rutas nuevas

- `index.php?page=chat_ia`: chat para empleados y contratistas.
- `index.php?page=admin_chat_ia`: administración de la base de conocimiento y sesiones.

### Nota de seguridad operativa

Para preguntas sobre diagnóstico, medicamentos, mortalidad alta, sacrificio humanitario, emergencia sanitaria o enfermedades de control oficial, el chat debe usarse solo como orientación. La decisión final debe escalarse al responsable técnico correspondiente.

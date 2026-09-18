# Plan de continuidad del proyecto

## 1. Propósito del documento

Este documento organiza el trabajo completo previsto para el sistema de cobranzas y marca qué procesos ya fueron construidos, cuáles están parcialmente definidos y cuáles todavía deben desarrollarse.

Su objetivo es servir como punto de continuidad entre etapas. Cada nueva funcionalidad debe actualizar este documento y conservar las reglas de negocio aquí descritas.

El documento no es un manual de usuario. Describe procesos, responsabilidades, dependencias y estado técnico del proyecto.

## 2. Alcance general del sistema

El proyecto busca controlar el ciclo completo de recuperación de cartera:

```text
Fuente externa
    -> clientes y obligaciones
    -> cuotas
    -> asignación de cartera
    -> gestión o visita
    -> pago, promesa, reprogramación o sin cobro
    -> recibo
    -> liquidación
    -> reportes
```

La solución prevista está compuesta por:

- un panel web administrativo;
- una base de datos central como fuente de verdad;
- una API para integraciones y operación móvil;
- una aplicación móvil para cobradores;
- procesos de sincronización offline-first;
- auditoría de las operaciones relevantes.

## 3. Convención de estados

- **Realizado**: existe código, estructura de datos y pruebas o validación funcional.
- **Parcial**: existe una base o una decisión de diseño, pero falta completar el proceso.
- **Pendiente**: todavía no existe la funcionalidad en el proyecto.
- **Bloqueado por definición**: requiere una decisión del negocio antes de implementarse correctamente.

## 4. Fase inicial: análisis y definición de la fuente

### 4.1 Analizar el archivo real de cartera — Realizado

Se revisó el archivo fuente utilizado por el negocio para conocer:

- nombre y estructura de la hoja;
- cantidad de columnas;
- cantidad de filas;
- campos de clientes;
- campos de obligaciones;
- campos calculados de mora y rangos;
- valores vacíos y datos incompletos;
- posibles duplicados.

El archivo analizado tiene 47 columnas y una fila por obligación o documento.

### 4.2 Definir el grano de la información — Realizado

Se estableció que una fila del archivo representa una obligación/documento asociado a un cliente.

El cliente se identifica inicialmente por `CodigoCliente`.

La obligación se identifica por:

```text
cliente + número de documento + fecha del documento
```

Esta regla evita duplicar una obligación cuando vuelve a aparecer en una importación posterior.

### 4.3 Separar datos base de datos calculados — Realizado

Se determinó que las columnas de antigüedad, mora, rangos y totales del Excel son datos derivados o de control del archivo fuente.

Los valores principales que se almacenan son:

- fechas;
- importe original;
- saldo;
- plazo;
- estado de origen;
- responsables;
- cliente;
- ubicación y contacto.

Los indicadores de mora y totales futuros deben recalcularse desde los datos base, evitando depender permanentemente de fórmulas o valores calculados del Excel.

### 4.4 Resolver duplicados de documentos — Parcial

El sistema ya tiene una clave técnica para evitar duplicados por cliente, documento y fecha.

Queda pendiente definir con el negocio qué hacer cuando el mismo número de documento aparece más de una vez con fechas, clientes o valores diferentes. Esta decisión debe quedar formalizada antes de construir reportes financieros definitivos.

### 4.5 Confirmar significado de estados de origen — Bloqueado por definición

Se detectaron valores de estado como `A`, `I`, `C`, `D`, `F`, `B` y `R`.

Todavía falta confirmar su significado operativo con el negocio antes de convertirlos en una clasificación interna o en un enum. Por ahora se conservan como valores provenientes de la fuente.

## 5. Fase actual: consolidación de cartera

### 5.1 Crear catálogo de clientes — Realizado

Existe la entidad `clientes` y el modelo `Cliente`.

El proceso permite crear o actualizar clientes usando `CodigoCliente` como identificador externo único.

La información actual incluye nombre, documento, contacto, dirección, ciudad, ubicación, coordenadas y límite de crédito.

### 5.2 Crear catálogo de obligaciones — Realizado

Existe la entidad `deudas` y el modelo `Deuda`.

La obligación conserva sus fechas, importes, saldo, responsables, estado y origen del registro.

La tabla tiene una restricción única para evitar duplicar la misma obligación según la clave definida.

### 5.3 Registrar cada importación — Realizado

Existe la entidad `importacion_carteras` y el modelo `ImportacionCartera`.

Cada proceso registra:

- archivo;
- hash SHA-256;
- hoja procesada;
- estado;
- filas leídas y omitidas;
- clientes creados y encontrados;
- obligaciones nuevas, actualizadas y sin cambios;
- saldo total reportado;
- fecha de proceso.

### 5.4 Importar archivos Excel — Realizado

Existe el comando `cartera:importar` y una entrada web para cargar el archivo.

El flujo actual:

1. recibe XLSX o XLS;
2. identifica el lector adecuado;
3. calcula el hash del archivo;
4. rechaza archivos repetidos;
5. valida exactamente las 47 columnas oficiales;
6. procesa las filas;
7. crea o actualiza clientes;
8. crea o actualiza obligaciones;
9. registra el resultado;
10. confirma o revierte la transacción.

### 5.5 Validar formato y datos mínimos — Realizado

Se rechaza un archivo si su encabezado no coincide exactamente con el formato oficial.

Por fila se validan como mínimos:

- código de cliente;
- nombre de cliente;
- número de documento;
- importe;
- saldo;
- fecha válida.

Las filas incompletas o inválidas se cuentan como omitidas, mientras que un error estructural del archivo detiene el proceso completo.

### 5.6 Evitar reprocesamiento del mismo archivo — Realizado

El hash SHA-256 y la restricción única de la base de datos evitan procesar dos veces el mismo contenido.

Cambiar saldos u otros valores genera un hash diferente y permite procesar el nuevo corte, siempre que el formato de columnas se conserve.

### 5.7 Ejecutar simulación — Realizado

El comando soporta el modo `--simular`.

En este modo se ejecuta la lectura, validación y cálculo de resultados, pero la transacción se revierte al final. No deben persistirse clientes, obligaciones, auditorías ni el registro de importación.

### 5.8 Auditar cambios de obligaciones — Realizado

Existe la entidad `actualizacion_deudas` y el modelo `ActualizacionDeuda`.

Cuando cambia un campo auditable de una obligación existente, se guardan los valores anteriores y nuevos. La auditoría se vincula con la importación que detectó el cambio.

### 5.9 Consultar y filtrar cartera — Realizado

La pantalla web de cartera permite consultar obligaciones junto con su cliente y filtrar por:

- texto libre;
- ciudad;
- vendedor;
- supervisor;
- estado.

También muestra un resumen calculado sobre el conjunto filtrado:

- cantidad de obligaciones;
- cantidad de clientes;
- saldo total reportado.

### 5.10 Consultar historial de importaciones — Realizado

El historial se muestra en un modal separado de la tabla principal.

Actualmente presenta los últimos procesos ordenados por fecha de proceso descendente, utilizando el identificador como desempate. Muestra filas procesadas, obligaciones nuevas, obligaciones actualizadas, estado y fecha.

### 5.11 Control de acceso del módulo — Realizado

Se definieron permisos específicos:

- `cartera.view`: consultar cartera e historial;
- `cartera.import`: cargar un nuevo archivo.

Las rutas web están protegidas con autenticación y permisos.

## 6. Procesos pendientes sobre la cartera

### 6.1 Manejo de obligaciones ausentes en un nuevo corte — Pendiente

Debe definirse qué significa que una obligación que existía en un corte anterior no aparezca en el nuevo archivo.

Opciones a decidir:

- marcarla como ausente del corte;
- marcarla como cerrada o retirada;
- conservarla visible con el último estado conocido;
- generar una novedad para revisión.

No se debe eliminar automáticamente una obligación sin una regla de negocio aprobada.

### 6.2 Recalcular mora y estado de vencimiento — Pendiente

Debe construirse una lógica propia para calcular:

- días de mora;
- estado vigente o vencido;
- rangos de mora;
- antigüedad;
- totales por cliente, vendedor, supervisor y cartera.

Esta lógica debe utilizar fechas, importe y saldo almacenados, no depender de las fórmulas del archivo externo.

### 6.3 Crear cuotas o planes de pago — Pendiente

El modelo actual maneja la obligación completa, pero todavía no existe un modelo de cuotas.

Debe definirse:

- si todas las obligaciones tienen cuotas;
- cómo se genera el plan;
- fechas programadas;
- capital, intereses y otros conceptos;
- estado de cada cuota;
- relación entre cuota y obligación;
- comportamiento cuando cambia el saldo externo.

### 6.4 Definir asignación de cartera — Pendiente

Debe construirse el proceso para asignar obligaciones a equipos, vendedores, supervisores o cobradores.

La asignación debe conservar:

- responsable;
- ámbito de cartera;
- fecha de inicio y fin;
- usuario que asignó;
- historial de reasignaciones;
- regla para evitar asignaciones simultáneas incompatibles.

Los responsables que vienen del archivo son datos de origen y no sustituyen necesariamente una asignación operativa interna.

### 6.5 Crear gestión de cobranza — Pendiente

Debe registrarse cada contacto o visita realizada sobre una obligación o cliente.

El proceso debe distinguir claramente entre:

- gestión realizada;
- promesa de pago;
- pago recibido;
- reprogramación;
- sin contacto;
- negativa de pago;
- dirección incorrecta;
- visita pendiente.

Una gestión no debe modificar por sí sola el saldo externo ni convertirse automáticamente en un pago.

### 6.6 Registrar operación móvil de campo — Pendiente

La futura aplicación móvil deberá permitir trabajar con cartera asignada incluso sin conexión.

Requisitos previstos:

- descarga de cartera asignada;
- almacenamiento local temporal;
- registro offline de gestiones;
- fecha y hora del dispositivo;
- GPS;
- evidencias o fotografías cuando correspondan;
- cola de sincronización;
- reintentos seguros;
- identificación idempotente de operaciones.

### 6.7 Crear API para sincronización — Pendiente

Debe definirse una API versionada para:

- autenticar la aplicación móvil;
- entregar cartera asignada;
- recibir gestiones;
- recibir pagos;
- enviar evidencias;
- consultar resultados de sincronización;
- resolver conflictos.

La API debe ser idempotente. Reenviar una operación por pérdida de conexión no debe duplicar gestiones ni pagos.

### 6.8 Registrar pagos — Pendiente

Los pagos deben existir como entidad propia y no sobrescribir directamente el saldo recibido desde la fuente externa.

Debe definirse:

- monto;
- fecha y hora;
- obligación o cuota afectada;
- medio de pago;
- usuario que registra;
- origen web o móvil;
- referencia externa;
- estado de validación;
- reversión o anulación.

La aplicación debe diferenciar saldo externo, pagos internos y saldo calculado para cobranza.

### 6.9 Registrar promesas y reprogramaciones — Pendiente

Debe existir un proceso para registrar:

- monto prometido;
- fecha prometida;
- cuotas o documentos incluidos;
- condiciones acordadas;
- resultado de la promesa;
- incumplimiento;
- nueva fecha o reprogramación.

Una promesa no debe contabilizarse como pago hasta que exista un pago confirmado.

### 6.10 Generar recibos — Pendiente

Para pagos confirmados debe generarse un recibo con numeración y trazabilidad.

La solución prevista contempla impresión desde dispositivos POS mediante Bluetooth y ESC/POS, además de una representación digital consultable.

Debe definirse:

- numeración;
- formato;
- datos obligatorios;
- reimpresión;
- anulación;
- control de duplicados;
- relación con liquidaciones.

### 6.11 Crear liquidaciones — Pendiente

Debe construirse el proceso para consolidar pagos y recibos por periodo, cobrador, vendedor o equipo.

Debe incluir:

- periodo;
- responsable;
- efectivo, transferencias u otros medios;
- total esperado;
- total entregado;
- diferencias;
- estado de liquidación;
- aprobación;
- auditoría de modificaciones.

### 6.12 Construir reportes operativos y financieros — Pendiente

Los reportes deben construirse después de cerrar las reglas de cartera, pagos y liquidaciones.

Reportes previstos:

- cartera total;
- cartera vencida;
- mora por rango;
- recuperación por periodo;
- promesas cumplidas e incumplidas;
- productividad por cobrador;
- pagos por medio;
- diferencias de liquidación;
- variaciones entre cortes externos.

## 7. Requisitos transversales pendientes

### 7.1 Auditoría completa — Parcial

La importación y los cambios de obligaciones ya tienen auditoría.

Debe ampliarse la trazabilidad a:

- asignaciones;
- gestiones;
- pagos;
- promesas;
- recibos;
- liquidaciones;
- anulaciones y correcciones.

Cada operación crítica debe registrar quién, cuándo, qué cambió y desde qué canal.

### 7.2 Reglas de calidad de datos — Parcial

Ya se validan estructura y campos mínimos del archivo.

Queda pendiente formalizar validaciones para:

- valores numéricos fuera de rango;
- fechas inconsistentes;
- saldos negativos;
- documentos duplicados con datos incompatibles;
- códigos de cliente que cambian de identidad;
- estados desconocidos;
- datos de contacto incompletos;
- obligaciones ausentes de un corte.

### 7.3 Pruebas de negocio — Parcial

La etapa de cartera ya tiene pruebas para acceso, filtros, importación, formato, duplicados, simulación y auditoría básica.

Deben agregarse pruebas para las siguientes etapas antes de habilitarlas:

- ausencia de obligaciones en nuevos cortes;
- recalculo de mora;
- asignaciones y reasignaciones;
- sincronización móvil repetida;
- pagos y reversos;
- promesas incumplidas;
- recibos y liquidaciones;
- permisos por rol y ámbito de cartera.

### 7.4 Rendimiento de importaciones grandes — Pendiente

La importación actual procesa filas dentro de una transacción única. Antes de manejar archivos mucho mayores debe evaluarse:

- procesamiento por lotes;
- límites de memoria;
- tiempos máximos de la petición web;
- ejecución en cola;
- reanudación de importaciones;
- reporte de progreso;
- bloqueo de importaciones simultáneas.

## 8. Orden recomendado para continuar

### Etapa A: cerrar reglas de cartera

1. Definir obligaciones ausentes en nuevos cortes.
2. Confirmar estados de origen.
3. Resolver duplicados de documentos.
4. Implementar cálculo propio de mora y vencimiento.
5. Ampliar validaciones de calidad.

### Etapa B: cuotas y asignaciones

6. Diseñar cuotas o planes de pago.
7. Diseñar equipos y responsables operativos.
8. Implementar asignación y reasignación.
9. Agregar historial de asignaciones.

### Etapa C: gestión de cobranza

10. Crear gestiones y resultados.
11. Crear promesas y reprogramaciones.
12. Crear agenda, visitas y evidencias.
13. Aplicar reglas de cartera asignada.

### Etapa D: pagos y documentos

14. Crear pagos internos separados del saldo externo.
15. Definir validación y reversión de pagos.
16. Generar recibos.
17. Crear liquidaciones.

### Etapa E: API y aplicación móvil

18. Diseñar contratos de API.
19. Implementar autenticación móvil.
20. Implementar descarga de cartera asignada.
21. Implementar cola offline y sincronización idempotente.
22. Integrar GPS, evidencias y recibos POS.

### Etapa F: reportes y cierre operativo

23. Crear reportes de cartera y mora.
24. Crear reportes de recuperación.
25. Crear reportes de productividad.
26. Crear reportes de liquidación.
27. Completar auditoría y pruebas de todo el ciclo.

## 9. Estado de referencia al cierre de esta etapa

La primera etapa funcional terminada es la consolidación de cartera desde Excel:

- existen clientes y obligaciones persistidos;
- existe importación web y por comando;
- existe validación exacta del formato oficial;
- existe control de archivos repetidos por hash;
- existe simulación sin persistencia;
- existe actualización de saldos y datos auditables;
- existe historial de importaciones;
- existen filtros y resumen de cartera;
- existe control de acceso específico;
- existe cobertura automatizada del flujo actual.

El siguiente bloque recomendado es cerrar las reglas de negocio de cartera antes de crear pagos, cuotas o operación móvil. Esas decisiones afectan la estructura de datos y deben quedar resueltas para evitar reconstrucciones posteriores.

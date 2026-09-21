# Plan de continuidad del proyecto

## 1. Propósito

Este documento organiza el trabajo del sistema de cobranzas y registra qué procesos están realizados, cuáles son parciales y cuáles están pendientes.

La aplicación pertenece a una empresa prestadora de servicios de cobranza. La empresa mandante entrega la cartera y recibe al final de la jornada los registros generados por nuestros cobradores.

## 2. Alcance vigente

El flujo operativo es:

```text
DOC_MADRE de la empresa mandante
    -> cartera operativa
    -> asignación a cobradores
    -> visita o gestión
    -> cobro y recibo
    -> exportación diaria
    -> aplicación en la base de la empresa mandante
```

La aplicación administra clientes, obligaciones, empresas mandantes, asignaciones, visitas, gestiones, cobros operativos, recibos y exportaciones.

La aplicación no calcula mora, no realiza análisis financiero, no liquida dinero y no reemplaza la base de datos de la empresa mandante. El DOC_MADRE es la fuente del saldo y estado externos. Nuestra aplicación registra la operación realizada y la entrega para que la empresa mandante la aplique en su propia base.

## 3. Convención de estados

- **Realizado**: existe código y pruebas o validación funcional.
- **Parcial**: existe una base, pero falta completar el proceso.
- **Pendiente**: todavía no existe la funcionalidad.
- **No aplica**: no pertenece al alcance operativo vigente.
- **Bloqueado por definición**: requiere una decisión del negocio.

## 4. Fuente y reglas de cartera

### 4.1 Analizar el archivo real de cartera — Realizado

Se revisó el archivo fuente para conocer hoja, columnas, filas, datos de clientes, obligaciones, valores calculados, vacíos y posibles duplicados.

El archivo analizado tiene 47 columnas y una fila por obligación o documento.

### 4.2 Definir el grano de la información — Realizado

Una fila representa una obligación asociada a un cliente.

El cliente se identifica por empresa mandante + `CodigoCliente`.

La obligación se identifica por:

```text
empresa mandante + cliente + número de documento + fecha del documento
```

### 4.3 Separar datos externos de datos operativos — Realizado

Se conservan como datos externos:

- fechas;
- importe original;
- saldo reportado;
- plazo;
- estado de origen;
- responsables informados;
- datos del cliente.

Las columnas de mora, antigüedad, rangos y totales del Excel no se convierten en análisis propios de la aplicación.

### 4.4 Resolver duplicados de documentos — Parcial

Existe una clave técnica por empresa, cliente, documento y fecha.

Queda pendiente definir con el negocio qué hacer cuando el mismo documento aparece con fechas, clientes o valores incompatibles, siempre que afecte la asignación o la gestión operativa.

### 4.5 Confirmar estados de origen — Bloqueado por definición

Se detectaron estados `A`, `I`, `C`, `D`, `F`, `B` y `R`.

Se conservan como valores de la fuente hasta confirmar qué significado operativo deben tener para los cobradores.

## 5. Consolidación de cartera

### 5.1 Empresas mandantes — Realizado

Existe `empresas_mandantes` y una pantalla administrativa para:

- registrar empresas;
- editar código y razón social;
- habilitar o deshabilitar empresas;
- consultar clientes y cortes asociados.

Permisos:

- `empresas.view`;
- `empresas.create`;
- `empresas.update`;
- `empresas.delete` preparado para futuras operaciones.

La primera empresa registrada para el proyecto fue BBO. Una empresa deshabilitada no aparece en el selector global ni puede recibir nuevas importaciones, pero conserva sus datos e historial.

### 5.2 Empresa activa global — Realizado

El usuario selecciona una empresa mandante desde la barra superior. La selección se conserva en sesión.

La cartera, los filtros, el historial y la importación web trabajan únicamente con la empresa activa. Esto evita mezclar información entre mandantes.

### 5.3 Catálogo de clientes — Realizado

Existe la entidad `clientes` y el modelo `Cliente`.

Los clientes se crean o actualizan usando empresa mandante + `CodigoCliente` como identificador externo. Dos empresas pueden utilizar el mismo código sin mezclarse.

### 5.4 Catálogo de obligaciones — Realizado

Existe la entidad `deudas` y el modelo `Deuda`.

La obligación conserva sus fechas, importes, saldo externo, responsables, estado y origen del registro.

### 5.5 Registrar cada importación — Realizado

Existe `importacion_carteras` y cada proceso registra:

- empresa mandante;
- archivo y hash SHA-256;
- hoja procesada;
- estado;
- filas leídas y omitidas;
- clientes creados y actualizados;
- obligaciones nuevas, actualizadas y sin cambios;
- obligaciones ausentes y reingresadas;
- saldo reportado;
- fecha de proceso.

### 5.6 Importar archivos Excel — Realizado

Existe el comando `cartera:importar` y la entrada web.

El flujo valida el formato oficial de 47 columnas, procesa filas, crea o actualiza clientes y obligaciones, audita cambios y confirma o revierte la transacción.

La pantalla muestra la empresa activa y una barra de progreso durante la carga del DOC_MADRE.

### 5.7 Validar formato y datos mínimos — Realizado

Se validan encabezados exactos y como mínimo:

- código de cliente;
- nombre de cliente;
- número de documento;
- importe;
- saldo;
- fecha válida.

### 5.8 Evitar reprocesamiento — Realizado

El hash SHA-256 impide procesar dos veces el mismo archivo.

### 5.9 Simulación — Realizado

El comando acepta `--simular` y revierte toda la transacción. No persiste empresas, clientes, obligaciones, auditorías ni importaciones.

### 5.10 Auditoría de obligaciones — Realizado

`actualizacion_deudas` conserva valores anteriores y nuevos cuando cambian datos externos de una obligación.

### 5.11 Consulta de cartera — Realizado

La pantalla permite consultar y filtrar por texto, ciudad, vendedor, supervisor y estado, siempre dentro de la empresa activa.

### 5.12 Historial de importaciones — Realizado

El historial se muestra en un modal con archivo, estado, filas, nuevas, actualizadas, ausentes, reingresadas y fecha de proceso.

### 5.13 Comparación de cortes — Realizado

Cada importación se compara con el último corte completado de la misma empresa.

Una obligación puede quedar como:

- `presente`;
- `ausente`;
- `reingresada`.

Una ausencia no significa pago, cierre ni eliminación. La obligación conserva su historial porque la empresa mandante puede haber gestionado el cobro por su propio canal.

## 6. Procesos operativos pendientes

### 6.1 Asignación de cartera — Pendiente

Debe permitir asignar clientes u obligaciones a equipos, supervisores, vendedores y cobradores.

Debe conservar responsable, ámbito, fechas, usuario que asigna, historial y regla contra asignaciones incompatibles.

### 6.2 Gestión de cobranza — Pendiente

Debe registrar cada visita o contacto y su resultado:

- gestión realizada;
- cobro registrado;
- promesa, si el negocio la requiere;
- reprogramación;
- sin contacto;
- negativa;
- dirección incorrecta;
- visita pendiente.

Una gestión no modifica el saldo externo.

### 6.3 Cobros operativos — Pendiente

Los cobros realizados por nuestra empresa se registrarán para exportación, no como liquidación contable definitiva.

Deben incluir monto, fecha, obligación, medio, usuario, canal, referencia, estado de exportación y correcciones o anulaciones operativas.

### 6.4 Recibos — Pendiente

Cada cobro registrado podrá generar un recibo numerado y trazable, con representación digital y futura integración POS si se requiere.

### 6.5 Exportación diaria — Pendiente

Al finalizar la jornada se debe generar un archivo para la empresa mandante con:

- empresa mandante;
- fecha de operación;
- cobrador;
- cliente y obligación;
- tipo y resultado de gestión;
- cobro registrado;
- recibo;
- observaciones y evidencias;
- identificador único idempotente;
- estado de exportación.

La empresa mandante aplicará estos registros en su propia base de datos.

## 7. Procesos fuera del alcance inicial

### 7.1 Cálculo de mora y análisis financiero — No aplica

La aplicación no calculará mora, antigüedad, rangos, recuperación, productividad financiera ni indicadores contables.

### 7.2 Cuotas y planes internos — No aplica inicialmente

No se crearán cuotas ni planes internos mientras la empresa mandante no los entregue o solicite expresamente.

### 7.3 Liquidaciones financieras — No aplica

La aplicación no controlará entrega de dinero ni realizará liquidaciones financieras. La empresa mandante conciliará sus registros en su propia base.

## 8. Operación móvil y API

### 8.1 Operación móvil de campo — Pendiente

Debe contemplar cartera asignada, trabajo offline, GPS, evidencias, cola de sincronización, reintentos e identificación idempotente.

### 8.2 API de sincronización — Pendiente

Debe definir autenticación móvil, descarga de cartera, recepción de gestiones/cobros, evidencias, resultados y conflictos.

## 9. Requisitos transversales

### 9.1 Auditoría completa — Parcial

La importación y cambios externos ya tienen auditoría. Debe ampliarse a asignaciones, gestiones, cobros, recibos, exportaciones y anulaciones.

### 9.2 Calidad de datos — Parcial

Queda pendiente formalizar validaciones de valores fuera de rango, fechas inconsistentes, saldos negativos, duplicados incompatibles, estados desconocidos, datos de contacto incompletos y obligaciones ausentes.

### 9.3 Pruebas de negocio — Parcial

La cartera y comparación de cortes tienen pruebas. Deben agregarse pruebas para asignaciones, gestiones, cobros, recibos, exportaciones repetidas o rechazadas y permisos por rol y ámbito.

### 9.4 Rendimiento — Pendiente

Antes de manejar archivos mucho mayores debe evaluarse procesamiento por lotes, límites de memoria, colas, reanudación, progreso y bloqueo de importaciones simultáneas.

## 10. Orden recomendado

1. Confirmar estados de origen que verá el cobrador.
2. Resolver duplicados que afecten la gestión.
3. Implementar equipos y asignación de cartera.
4. Implementar gestiones y resultados.
5. Implementar cobros operativos.
6. Generar recibos.
7. Diseñar y generar exportación diaria.
8. Agregar identificadores idempotentes y control de entregas.
9. Implementar aplicación móvil y sincronización.
10. Completar auditoría y pruebas del ciclo operativo.

## 11. Estado de referencia

La consolidación de cartera está funcional:

- empresa mandante BBO registrada y aislada;
- empresa activa seleccionable por sesión;
- clientes y obligaciones persistidos por empresa;
- importación web y por comando;
- validación exacta del formato;
- control de archivos repetidos;
- simulación sin persistencia;
- auditoría de cambios externos;
- comparación de cortes con ausencias y reingresos;
- filtros e historial;
- barra de progreso de carga;
- permisos y pruebas automatizadas.

El siguiente bloque funcional es asignación, gestión de cobranza, cobro operativo, recibo y exportación diaria a la empresa mandante.

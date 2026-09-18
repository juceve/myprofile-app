# Módulo de cartera

## 1. Propósito y alcance

El módulo de cartera recibe un archivo Excel proveniente de una fuente externa, valida que conserve la estructura esperada, transforma sus filas en clientes y obligaciones, y registra cada proceso como un corte auditable.

El objetivo de esta primera etapa es establecer una fuente confiable para consultar:

- clientes incluidos en la cartera;
- obligaciones y documentos asociados;
- importes, saldos y fechas reportadas;
- responsables comerciales;
- resultado de cada importación;
- variaciones detectadas entre una importación y otra.

Este módulo todavía no gestiona pagos, gestiones de cobranza, promesas, asignaciones ni recibos. Esas funciones podrán agregarse sobre la información que aquí se consolida.

## 2. Conceptos principales

### Cliente

Representa al titular de una o más obligaciones. Se identifica mediante `CodigoCliente`, almacenado como `codigo_externo`.

El registro conserva información básica proveniente del archivo:

- nombre;
- documento de identidad;
- teléfono;
- dirección;
- ciudad;
- tipo de ubicación;
- coordenadas;
- límite de crédito.

El código externo es único. Si el mismo cliente vuelve a aparecer en otra importación, sus datos se actualizan en lugar de crear un segundo registro.

### Obligación

Representa el documento o deuda reportado en una fila del archivo. Se relaciona con un cliente y conserva:

- número de documento;
- fecha del documento;
- fecha de vencimiento;
- importe original;
- saldo actual;
- plazo;
- fecha del último pago reportado;
- estado de origen;
- jefe, supervisor y vendedor;
- fecha de carga;
- archivo y fila de origen.

La combinación que identifica una obligación es:

`cliente + numero_documento + fecha_documento`

Esta regla permite actualizar una obligación existente cuando vuelve a aparecer, sin duplicarla.

### Importación

Representa un proceso completo de recepción y procesamiento de un archivo. Guarda el nombre, hash, hoja, estado, cantidades resultantes, saldo total reportado y fecha de proceso.

El hash SHA-256 identifica el contenido del archivo. Si se intenta procesar dos veces el mismo archivo, la segunda operación se rechaza antes de modificar la información.

### Actualización de obligación

Cuando una importación modifica valores auditables de una obligación existente, se genera un registro de auditoría con:

- tipo de cambio;
- valores anteriores;
- valores nuevos;
- importación que detectó la variación;
- obligación afectada.

Esto permite reconstruir qué información externa cambió entre cortes.

## 3. Formato de entrada

El archivo aceptado puede ser XLSX o XLS y debe tener una hoja activa con exactamente las 47 columnas del formato oficial, en la primera fila y en el siguiente orden:

1. `Fecha`
2. `NumDoc`
3. `Importe`
4. `Saldo`
5. `Vence`
6. `Antiguedad`
7. `Anticuacion`
8. `Rango`
9. `Cliente`
10. `cliLugar`
11. `entNombreJefeVendedor`
12. `entNombreSupervisor`
13. `entNombreVendedor`
14. `Plazo`
15. `FechaUltimoPago`
16. `ciuNombre`
17. `CodigoCliente`
18. `LimiteCredito`
19. `rutId`
20. `CoordenadaX`
21. `CoordenadaY`
22. `Telefono`
23. `Estado`
24. `DIRECCION`
25. `FECHA_CARGA`
26. `Dias`
27. `%`
28. `Marca-1`
29. `01 - 30`
30. `31 - 60`
31. `61 - 90`
32. `91 - 120`
33. `121 - 150`
34. `151 - 180`
35. `181-210`
36. `211-250`
37. `mas 251`
38. `Mas 60 menor 120`
39. `total mas de 120 dias`
40. `total mas de 180 dias`
41. `Total Vigente`
42. `Total Vencido`
43. `Total Cartera`
44. `Provisión`
45. `Plazo2`
46. `Corte Plazos`
47. `Dif.`

La validación es exacta. Se rechaza el archivo si falta una columna, sobra una columna, cambia un nombre o cambia el orden esperado. Esta regla permite aceptar una copia del archivo oficial con cambios en los saldos, pero evita procesar documentos de otra estructura.

Las columnas calculadas de antigüedad, mora, rangos y totales se validan como parte de la estructura, pero la primera etapa no las persiste como campos independientes. Los datos operativos principales se toman de las columnas base de clientes y obligaciones.

## 4. Flujo técnico de importación

El proceso se ejecuta mediante el comando:

```text
php artisan cartera:importar <ruta-del-archivo>
```

También puede ejecutarse en modo simulación:

```text
php artisan cartera:importar <ruta-del-archivo> --simular
```

### 4.1 Resolución y lectura

El sistema busca el archivo recibido en la ruta indicada y en la raíz de la aplicación. PhpSpreadsheet identifica el lector correspondiente al tipo de archivo y carga la hoja activa usando lectura de datos.

### 4.2 Identificación del archivo

Antes de importar se calcula el hash SHA-256 del archivo. Ese hash se utiliza para impedir que el mismo contenido sea procesado más de una vez.

En una importación normal, el archivo se resguarda en almacenamiento local privado bajo una ruta derivada del hash. El registro de importación conserva la ruta del resguardo.

### 4.3 Validación de estructura

Se leen los encabezados de la primera fila y se comparan con la lista exacta de 47 columnas. Esta validación se realiza antes de comenzar a crear o modificar clientes y obligaciones.

Si la estructura no coincide, el proceso termina con un mensaje específico y no debe crear un corte válido ni modificar la cartera.

### 4.4 Transacción

La creación o actualización de datos se ejecuta dentro de una transacción de base de datos. Si ocurre un error inesperado, la transacción se revierte para evitar una importación parcial.

En modo simulación también se procesa el archivo y se calculan los resultados, pero la transacción termina con `rollback`. Por eso la simulación permite revisar el comportamiento sin persistir clientes, obligaciones, auditorías ni el registro de importación.

### 4.5 Procesamiento de filas

Cada fila posterior a la cabecera se cuenta como fila leída. Para ser procesada debe tener, como mínimo:

- `CodigoCliente`;
- `Cliente`;
- `NumDoc`;
- `Importe`;
- `Saldo`;
- una fecha válida en `Fecha`.

Una fila que no cumple estos requisitos se cuenta como omitida. La omisión de una fila no cancela automáticamente todo el archivo.

### 4.6 Cliente

El sistema busca el cliente por `CodigoCliente` y aplica una operación de actualización o creación. De esta forma, el cliente puede recibir nuevos datos en cada corte sin duplicarse.

### 4.7 Obligación

La obligación se busca por cliente, número de documento y fecha del documento.

- Si no existe, se crea.
- Si existe, se actualizan sus valores provenientes del archivo.
- Si no cambian los campos auditables, se clasifica como sin cambios.
- Si cambia al menos un campo auditable, se clasifica como actualizada y se crea una auditoría.

Los campos auditables actuales son fechas, importes, saldo, plazo, estado, responsables y fecha de carga.

## 5. Resultado de cada proceso

Cada importación conserva contadores para:

- filas leídas;
- filas omitidas;
- clientes creados;
- clientes encontrados o actualizados;
- obligaciones creadas;
- obligaciones actualizadas;
- obligaciones sin cambios;
- saldo total reportado.

El estado se marca como completado cuando el proceso termina correctamente. Los procesos fallidos no deben presentarse como importaciones completadas.

En el historial de la pantalla de cartera se muestran los últimos procesos ordenados por `procesado_en` descendente. Si dos procesos tienen la misma fecha, se utiliza el identificador en orden descendente como desempate.

## 6. Consulta de cartera

La pantalla de cartera consulta las obligaciones junto con su cliente y muestra:

- cliente;
- código externo y ciudad;
- número de documento;
- vencimiento;
- responsable principal;
- estado de origen;
- saldo reportado.

Incluye filtros por:

- texto libre sobre cliente, código, documento o teléfono;
- ciudad;
- vendedor;
- supervisor;
- estado.

El resumen calcula sobre el conjunto filtrado:

- cantidad de obligaciones;
- cantidad de clientes distintos;
- suma del saldo actual.

La información está paginada y se ordena por fecha de vencimiento e identificador de obligación.

## 7. Acceso al módulo

El módulo utiliza dos permisos específicos:

- `cartera.view`: permite consultar la cartera y el historial;
- `cartera.import`: permite abrir el formulario y enviar nuevos archivos.

La ruta de consulta es:

```text
GET /cartera
```

La ruta de importación es:

```text
POST /cartera/importar
```

El formulario de importación está separado visualmente de la tabla principal y se abre en un modal. El historial también se consulta en un modal independiente. La interfaz muestra mensajes de éxito o error devueltos por el proceso.

## 8. Reglas de integridad actuales

1. Un archivo idéntico no puede procesarse dos veces.
2. Un archivo con formato diferente al oficial se rechaza.
3. Una obligación no se duplica si conserva su clave de cliente, documento y fecha.
4. Las modificaciones de obligaciones existentes quedan auditadas.
5. Un error inesperado revierte la transacción.
6. El modo simulación no persiste cambios.
7. Los archivos resguardados se identifican por hash.
8. El saldo almacenado representa el saldo reportado por la fuente externa. Los pagos internos todavía no forman parte de este módulo.

## 9. Estado actual y próximos puntos de ampliación

Hasta esta etapa está construido el ciclo de recepción, validación, consolidación, actualización y consulta de cartera.

La estructura queda preparada para ampliar el sistema con procesos posteriores, entre ellos:

- gestión de pagos separados del saldo externo;
- asignación de obligaciones a equipos o cobradores;
- gestiones y visitas;
- promesas de pago;
- recibos y liquidaciones;
- reportes operativos y financieros;
- trazabilidad detallada por usuario y proceso;
- validaciones de negocio adicionales sobre duplicados y estados.

Estas funciones no deben alterar la responsabilidad actual del campo `saldo_actual`: en esta etapa representa exclusivamente el último saldo recibido desde el archivo fuente.

## 10. Componentes principales

| Componente | Responsabilidad |
| --- | --- |
| `ImportarCartera` | Lee, valida y procesa el archivo Excel. |
| `CarteraController` | Expone consulta, filtros e importación desde la web. |
| `Cliente` | Modelo del titular de la obligación. |
| `Deuda` | Modelo de la obligación importada. |
| `ImportacionCartera` | Registro de cada proceso de importación. |
| `ActualizacionDeuda` | Auditoría de cambios detectados en obligaciones. |
| `cartera/index.blade.php` | Consulta, filtros, resumen, importación e historial. |
| `CarteraTest` | Pruebas de acceso, filtros, historial y flujo web. |
| `ImportarCarteraTest` | Pruebas de importación, duplicados, simulación y formato. |

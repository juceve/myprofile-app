# Parte teórica cobranzas

**Fuente:** https://chatgpt.com/share/6aad8ae3-3728-83e9-89a9-dffabe06d055

**Tipo de extracción:** texto visible del chat compartido, ordenado por mensaje y estructura temática.

---

## Mensaje del usuario

Dame la parte teórica del proyecto que diseñamos para desarrollar una aplicación de cobranzas.

---

## Respuesta de ChatGPT

Sí. Retomando el diseño que habíamos trabajado, la aplicación de cobranzas se planteó como un **sistema integral de gestión de cartera**, con **Laravel + PostgreSQL** como backend y administración, y **Flutter** como aplicación móvil para los cobradores, especialmente por las necesidades de **GPS, funcionamiento offline, Bluetooth e impresión POS**.

## 1. Nombre conceptual del proyecto

**Sistema Integral de Gestión y Control de Cobranzas**

El sistema tiene como finalidad administrar el ciclo completo de una deuda, desde que una obligación ingresa a la cartera hasta su cobro, seguimiento, liquidación y generación de reportes.

El concepto central es:

> **Cartera → Asignación → Gestión de cobranza → Cobro/Promesa → Control → Liquidación → Reportes**

---

## 2. Planteamiento del problema

En un proceso tradicional de cobranzas, la información puede encontrarse distribuida entre archivos Excel, documentos físicos, registros manuales y diferentes sistemas.

Esto genera problemas como:

- Dificultad para conocer el estado real de una deuda.
- Asignaciones manuales de clientes a cobradores.
- Falta de seguimiento de las visitas.
- Dificultad para controlar los pagos realizados.
- Riesgo de duplicidad o pérdida de información.
- Falta de evidencia de las visitas realizadas.
- Imposibilidad de trabajar adecuadamente cuando el cobrador no tiene Internet.
- Dificultad para controlar el dinero recaudado.
- Falta de información consolidada para supervisores y administradores.

El proyecto busca centralizar esta información y establecer un **flujo controlado y auditable de cobranza**.

---

## 3. Objetivo general

Desarrollar una aplicación informática para **administrar, controlar y dar seguimiento al proceso de cobranza**, permitiendo gestionar clientes, cartera, deudas, asignaciones, visitas, pagos, promesas de pago, recibos y liquidaciones, incorporando herramientas móviles para facilitar el trabajo de los cobradores en campo.

---

## 4. Objetivos específicos

### 4.1 Administrar clientes

Registrar y consultar la información de los clientes o deudores:

- Datos personales.
- Documento de identidad.
- Teléfonos.
- Direcciones.
- Contactos.
- Información adicional necesaria para la gestión.

### 4.2 Administrar la cartera

Centralizar las obligaciones pendientes de los clientes.

La cartera debe permitir conocer:

- Deudor.
- Monto original.
- Saldo pendiente.
- Número de cuotas.
- Cuotas vencidas.
- Fechas de vencimiento.
- Estado de la deuda.
- Historial de pagos.

### 4.3 Asignar cartera

Permitir que la administración o supervisión distribuya la cartera entre los responsables de cobranza.

La asignación debe permitir determinar:

> **qué cobrador → qué clientes → qué obligaciones → qué periodo**

Tiene bajo su responsabilidad.

### 4.4 Gestionar visitas

Registrar cada interacción realizada con un cliente.

Una visita puede terminar en:

- Cobro.
- Cobro parcial.
- Promesa de pago.
- Reprogramación.
- Cliente no localizado.
- Rechazo de pago.
- Sin contacto.
- Nueva visita requerida.

### 4.5 Registrar pagos

Registrar los cobros efectuados, incluyendo:

- Fecha.
- Hora.
- Monto.
- Deuda relacionada.
- Cuota.
- Usuario que realizó el cobro.
- Forma de pago.
- Observaciones.
- Recibo correspondiente.

### 4.6 Generar comprobantes

Después de un cobro, el sistema debe poder generar un recibo para el cliente.

La aplicación móvil podrá enviar el comprobante a una **impresora POS Bluetooth mediante ESC/POS**.

### 4.7 Controlar la actividad del cobrador

Registrar información relacionada con el trabajo realizado en campo:

- Visitas.
- Cobros.
- Ubicación GPS.
- Evidencias.
- Promesas.
- Resultados de gestión.
- Liquidaciones.

### 4.8 Permitir trabajo sin conexión

La aplicación móvil debe seguir funcionando cuando el cobrador no tenga Internet.

La información se almacena temporalmente en el dispositivo y posteriormente se sincroniza con el servidor.

Este concepto se definió como **offline-first**.

---

## 5. Actores del sistema

Se definieron cuatro actores principales:

| Actor | Función principal |
| --- | --- |
| Administrador | Configuración y administración general |
| Supervisor | Control y seguimiento de cobradores y cartera |
| Vendedor | Gestión comercial, asignación y seguimiento según el flujo definido |
| Cobrador | Trabajo de campo, visitas y cobros |

### Administrador

Tiene funciones relacionadas con:

- Usuarios.
- Roles.
- Permisos.
- Configuración.
- Catálogos.
- Reportes generales.
- Control del sistema.

### Supervisor

Es responsable del control operativo:

- Revisar cartera.
- Supervisar asignaciones.
- Consultar gestiones.
- Revisar cobros.
- Controlar cobradores.
- Revisar liquidaciones.
- Analizar indicadores.

### Vendedor

Participa en la gestión de clientes y cartera de acuerdo con las responsabilidades definidas para el proceso comercial.

### Cobrador

Es el usuario que trabaja principalmente desde la aplicación móvil:

- Consulta su cartera.
- Selecciona el cliente.
- Realiza la visita.
- Registra el resultado.
- Registra el cobro o compromiso.
- Genera el recibo.
- Sincroniza la información.

---

## 6. Módulos principales

La aplicación se estructuró conceptualmente en los siguientes módulos:

### 6.1 Seguridad y usuarios

- Autenticación.
- Usuarios.
- Roles.
- Permisos.
- Sesiones.
- Control de acceso.

La implementación inicial en Laravel quedó orientada a **Laravel Breeze/Livewire + Spatie Permission**.

### 6.2 Clientes

Permite administrar:

- Clientes/deudores.
- Direcciones.
- Teléfonos.
- Contactos.
- Información complementaria.

### 6.3 Cartera

Es uno de los módulos centrales.

Representa las obligaciones que deben ser gestionadas.

Conceptualmente:

```text
CLIENTE
└── DEUDA
    ├── Cuota 1
    ├── Cuota 2
    ├── Cuota 3
    └── ...
```

La cartera debe reflejar permanentemente el estado financiero de las obligaciones.

### 6.4 Asignaciones

Permite entregar determinadas obligaciones a un responsable.

Ejemplo:

```text
Cartera
↓
Supervisor
↓
Cobrador
↓
Clientes asignados
↓
Deudas asignadas
```

Esto permite posteriormente medir cuánto recibió un cobrador y qué resultados obtuvo.

### 6.5 Gestión de cobranza

Es el núcleo operativo.

Cada visita genera una **gestión**.

Ejemplo:

```text
Cliente
↓
Visita
↓
Resultado
├── Pago
├── Pago parcial
├── Promesa
├── Reprogramación
├── No localizado
└── Sin cobro
```

### 6.6 Pagos

Registra el dinero efectivamente recibido.

Es importante separar conceptualmente:

> **Gestión ≠ Pago**

Una visita puede generar una promesa sin que exista dinero recibido.

Por ejemplo:

```text
Visita
↓
"Cliente promete pagar Bs 500 el viernes"
↓
No existe todavía un pago
```

Cuando posteriormente paga:

```text
Pago
↓
Bs 500
↓
Actualización del saldo
```

### 6.7 Recibos

El recibo representa el comprobante de un pago.

El flujo previsto es:

```text
Cobro
↓
Registrar pago
↓
Generar recibo
↓
Generar formato ESC/POS
↓
Bluetooth
↓
Impresora POS
```

### 6.8 Liquidación

Permite controlar el dinero recaudado por el cobrador.

Conceptualmente:

```text
Cobrador
↓
Cobros realizados
↓
Total recaudado
↓
Liquidación
↓
Entrega / cierre
```

Esto permite comparar lo que el sistema registra contra lo efectivamente liquidado.

### 6.9 Reportes e indicadores

El sistema debe permitir obtener información como:

- Cartera total.
- Cartera pendiente.
- Cobros realizados.
- Cobros por periodo.
- Cobros por cobrador.
- Clientes visitados.
- Clientes no localizados.
- Promesas de pago.
- Promesas cumplidas/incumplidas.
- Recuperación de cartera.
- Liquidaciones.

---

## 7. Aplicación web y aplicación móvil

Una decisión importante del diseño fue **no intentar hacer todo en una sola aplicación**.

Se planteó una arquitectura dividida:

```text
┌──────────────────────┐
│      PostgreSQL      │
└──────────▲───────────┘
           │
┌──────────┴───────────┐
│       Laravel        │
│      API / Web       │
└───────▲───────▲──────┘
        │       │
  ┌─────┘       └─────┐
  │                   │
┌─┴───────────┐ ┌─────┴──────────┐
│ Panel Web   │ │ Flutter App    │
│ Admin       │ │ Cobradores     │
└─────────────┘ └────────────────┘
```

### Laravel

Se encarga de:

- Usuarios.
- Seguridad.
- Clientes.
- Cartera.
- Deudas.
- Asignaciones.
- Pagos.
- Reportes.
- Administración.
- API.
- Sincronización.

### Flutter

Se encarga principalmente de:

- Trabajo de campo.
- GPS.
- Cámara/evidencias.
- Almacenamiento local.
- Funcionamiento offline.
- Sincronización.
- Bluetooth.
- Impresión POS.

---

## 8. Funcionamiento offline

Esta es una característica fundamental del proyecto.

El cobrador no debería quedar bloqueado porque perdió Internet.

El flujo sería:

```text
SERVIDOR
   │
   │ sincronización
   ▼
┌─────────────┐
│  TELÉFONO   │
│   Flutter   │
└──────┬──────┘
       │ SIN INTERNET
       ▼
Datos locales
       │
       │ Se realizan las gestiones
       ▼
Cola de cambios
       │ cuando vuelve Internet
       ▼
SERVIDOR
```

La aplicación mantiene una **cola de sincronización** para operaciones realizadas mientras está desconectada.

---

## 9. Geolocalización y evidencias

Una de las características previstas es registrar la ubicación de determinadas operaciones.

Por ejemplo:

```text
Visita
├── Cliente
├── Fecha
├── Hora
├── Resultado
├── Coordenadas GPS
├── Observación
└── Evidencia fotográfica
```

Esto permite generar trazabilidad sobre las actividades realizadas en campo.

No significa simplemente “guardar GPS”; el objetivo es relacionar la ubicación con una **gestión concreta**.

---

## 10. Reglas de negocio principales

### Regla 1

Una deuda pertenece a un cliente/deudor.

### Regla 2

Una deuda puede tener múltiples cuotas.

### Regla 3

Una cuota puede recibir uno o varios pagos dependiendo del modelo de cobranza.

### Regla 4

Un pago debe estar relacionado con una obligación.

### Regla 5

Una visita debe registrar un resultado.

### Regla 6

Una promesa de pago no constituye un pago hasta que el dinero sea efectivamente registrado.

### Regla 7

Un cobrador solamente debe gestionar la cartera que le fue asignada.

### Regla 8

Las operaciones realizadas por el cobrador deben quedar auditadas.

### Regla 9

Las operaciones realizadas offline deben sincronizarse posteriormente.

### Regla 10

No debe permitirse duplicar una operación durante una sincronización.

### Regla 11

El saldo de una deuda debe actualizarse a partir de los pagos registrados.

### Regla 12

Los recibos deben estar relacionados con pagos reales.

### Regla 13

Una liquidación debe poder relacionarse con los cobros realizados por el cobrador.

### Regla 14

Las acciones importantes deben mantener trazabilidad histórica.

---

## 11. Macroproceso del sistema

El proceso completo que habíamos definido puede representarse así:

```text
┌─────────────────┐
│ Archivo / Fuente│
│ de deuda        │
└────────┬────────┘
         ↓
┌─────────────────┐
│ Actualización   │
│ de cartera      │
└────────┬────────┘
         ↓
┌─────────────────┐
│ Asignación      │
│ de cartera      │
└────────┬────────┘
         ↓
┌─────────────────┐
│ Visita          │
└────────┬────────┘
         ↓
   ┌─────┼─────┐
   ↓     ↓     ↓
 COBRO PROMESA SIN COBRO
   │     │     │
   ↓     ↓     ↓
 RECIBO SEGUIMIENTO REVISITA
   │     │     │
   └─────┼─────┘
         ↓
┌─────────────────┐
│ Control         │
└────────┬────────┘
         ↓
┌─────────────────┐
│ Cierre /        │
│ Liquidación     │
└────────┬────────┘
         ↓
┌─────────────────┐
│ Reportes        │
└─────────────────┘
```

---

## 12. Arquitectura tecnológica

| Capa | Tecnología |
| --- | --- |
| Backend | Laravel |
| Base de datos | PostgreSQL |
| Panel administrativo | Laravel + Livewire |
| Autenticación | Laravel Breeze |
| Roles/permisos | Spatie Permission |
| Aplicación móvil | Flutter |
| Comunicación | API REST |
| GPS | Flutter |
| Cámara | Flutter |
| Almacenamiento local | Flutter |
| Bluetooth | Flutter |
| Impresión | ESC/POS |
| Impresora | POS Bluetooth |

La ventaja de esta arquitectura es que Laravel mantiene la **fuente central de verdad**, mientras Flutter se especializa en las necesidades del trabajo de campo.

---

## 13. Auditoría y trazabilidad

Un requisito importante del sistema es que las operaciones críticas no simplemente “desaparezcan” o sean modificadas sin dejar rastro.

Se debe poder responder preguntas como:

> ¿Quién registró este pago?
>
> ¿Cuándo se registró?
>
> ¿A qué cliente corresponde?
>
> ¿Qué deuda afectó?
>
> ¿Qué cobrador realizó la gestión?
>
> ¿Dónde se realizó la visita?
>
> ¿Cuánto dinero recaudó?
>
> ¿Cuándo fue liquidado?

Por eso el sistema debe considerar **auditoría y trazabilidad como características estructurales**, no como algo agregado al final.

---

## 14. Principio fundamental del diseño

El sistema no se debe construir simplemente como:

> “Una aplicación que registra pagos.”

El concepto correcto es:

> **Una plataforma para controlar todo el ciclo de recuperación de una cartera.**

Por eso la entidad central no es únicamente el pago.

El flujo conceptual es:

```text
CLIENTE
   ↓
DEUDA
   ↓
CARTERA
   ↓
ASIGNACIÓN
   ↓
GESTIÓN
   ↓
RESULTADO
   ├── PAGO
   ├── PROMESA
   ├── REPROGRAMACIÓN
   └── SIN COBRO
   ↓
SEGUIMIENTO
   ↓
COBRO
   ↓
RECIBO
   ↓
LIQUIDACIÓN
```

---

## 15. Estado actual del desarrollo en el chat original

Lo importante para retomar el proyecto es que **no habíamos llegado todavía a crear el modelo definitivo de cartera/deudas**.

La base técnica de Laravel quedó planteada para:

- autenticación;
- usuarios;
- sesiones;
- roles;
- permisos;
- administrador;
- supervisor;
- vendedor;
- cobrador.

Y se había decidido **no crear todavía las tablas definitivas de cartera, deudas, asignaciones, visitas, pagos y recibos hasta trabajar con el Excel real de las deudas**, porque ese archivo determinaría qué información existe realmente y cómo debe modelarse.

Eso es importante: no conviene inventar ahora un modelo de deuda genérico y después intentar adaptar el Excel a la fuerza.

La siguiente etapa lógica del proyecto era tomar el **Excel real de cartera/deudas**, analizar sus columnas y, a partir de él, construir el **modelo de datos definitivo**:

```text
clientes → deudas → cuotas → asignaciones → gestiones → pagos → recibos → liquidaciones
```

---

## Opciones planteadas en el chat original

- Desarrollar el modelo de datos definitivo basado en el Excel real.
- Detallar el diseño e integración del módulo de asignaciones.
- Planificar el próximo sprint de desarrollo con foco en sincronización offline.

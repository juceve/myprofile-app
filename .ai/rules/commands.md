---
paths:
  - 'app/Console/Commands/**'
---

# Commands

## DOC_MADRE como saldo autoritativo
Cada importación DOC_MADRE debe crear un corte trazable con hash y archivo resguardado, y auditar las variaciones de deuda. `deudas.saldo_actual` solo refleja el origen externo; futuros cobros internos deben persistirse en entidades separadas y nunca sobrescribirlo.

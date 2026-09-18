---
paths:
  - 'routes/**'
---

# Routes

## Permisos por módulo en rutas
Toda vista o acción administrativa nueva debe tener permisos Spatie explícitos, registrados en RolePermissionSeeder y aplicados con middleware permission:. El rol Admin se sincroniza con todos los permisos al ejecutar el seeder.

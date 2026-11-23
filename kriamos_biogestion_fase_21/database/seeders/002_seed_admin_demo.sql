USE kriamos_biogestion_ica;

-- Usuario demo opcional. En Fase 03 se implementará password_hash() real desde PHP.
-- Este archivo se conserva separado por si se requiere recrear un usuario inicial.

INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, cargo)
SELECT id, 'Administrador', 'Kriamos', 'admin@kriamos.com.co', NULL, 'Administrador del sistema'
FROM roles
WHERE nombre = 'Administrador del sistema'
LIMIT 1;

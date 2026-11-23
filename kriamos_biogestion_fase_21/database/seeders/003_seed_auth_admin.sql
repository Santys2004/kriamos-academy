USE kriamos_biogestion_ica;

-- Usuario administrador demo para Fase 03.
-- Correo: admin@kriamos.com.co
-- Contraseña temporal: Kriamos2026*
-- Cambiar antes de producción.

UPDATE usuarios
SET password_hash = '$2y$12$RX35XgG7eAYyyuczSPdFDeiY5ngpumDuZztoF7T3c.tugUYFVCXRC',
    estado = 'activo',
    debe_cambiar_password = 1,
    password_changed_at = NOW()
WHERE email = 'admin@kriamos.com.co';

INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, cargo, estado, debe_cambiar_password, password_changed_at)
SELECT r.id, 'Administrador', 'Kriamos', 'admin@kriamos.com.co', '$2y$12$RX35XgG7eAYyyuczSPdFDeiY5ngpumDuZztoF7T3c.tugUYFVCXRC', 'Administrador del sistema', 'activo', 1, NOW()
FROM roles r
WHERE r.nombre = 'Administrador del sistema'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@kriamos.com.co')
LIMIT 1;

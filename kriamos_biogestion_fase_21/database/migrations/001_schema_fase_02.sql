CREATE DATABASE IF NOT EXISTS kriamos_biogestion_ica
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE kriamos_biogestion_ica;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS evidencias;
DROP TABLE IF EXISTS documentos;
DROP TABLE IF EXISTS certificaciones_granja;
DROP TABLE IF EXISTS firmas_digitales;
DROP TABLE IF EXISTS formatos_registros;
DROP TABLE IF EXISTS tipos_formato;
DROP TABLE IF EXISTS ciclos_productivos;
DROP TABLE IF EXISTS fases_ciclo;
DROP TABLE IF EXISTS galpones;
DROP TABLE IF EXISTS granjas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS rol_permiso;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS estados_documentales;
DROP TABLE IF EXISTS categorias_documentales;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permisos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(80) NOT NULL,
    accion VARCHAR(80) NOT NULL,
    codigo VARCHAR(160) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rol_permiso (
    rol_id BIGINT UNSIGNED NOT NULL,
    permiso_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permiso_roles FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rol_permiso_permisos FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id BIGINT UNSIGNED NOT NULL,
    nombres VARCHAR(120) NOT NULL,
    apellidos VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    cargo VARCHAR(120) NULL,
    telefono VARCHAR(40) NULL,
    documento_identidad VARCHAR(40) NULL,
    estado ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
    ultimo_ingreso_at DATETIME NULL,
    intentos_fallidos INT UNSIGNED NOT NULL DEFAULT 0,
    debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE granjas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(160) NOT NULL,
    sigla VARCHAR(10) NOT NULL UNIQUE,
    codigo_interno VARCHAR(40) NULL,
    ubicacion VARCHAR(255) NULL,
    municipio VARCHAR(120) NULL,
    departamento VARCHAR(120) NULL,
    capacidad_pollos INT UNSIGNED NOT NULL,
    numero_galpones INT UNSIGNED NOT NULL,
    responsable_sanitario_id BIGINT UNSIGNED NULL,
    administrador_granja_id BIGINT UNSIGNED NULL,
    estado ENUM('activa','inactiva','suspendida','en_auditoria') NOT NULL DEFAULT 'activa',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_granjas_resp_sanitario FOREIGN KEY (responsable_sanitario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_granjas_admin FOREIGN KEY (administrador_granja_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE galpones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    granja_id BIGINT UNSIGNED NOT NULL,
    codigo VARCHAR(40) NOT NULL,
    numero INT UNSIGNED NOT NULL,
    capacidad_pollos INT UNSIGNED NULL,
    area_m2 DECIMAL(10,2) NULL,
    estado ENUM('activo','inactivo','mantenimiento','vacio_sanitario') NOT NULL DEFAULT 'activo',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_galpon_granja_numero (granja_id, numero),
    UNIQUE KEY uq_galpon_codigo (codigo),
    CONSTRAINT fk_galpones_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id)
) ENGINE=InnoDB;

CREATE TABLE fases_ciclo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE ciclos_productivos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    granja_id BIGINT UNSIGNED NOT NULL,
    galpon_id BIGINT UNSIGNED NOT NULL,
    fase_ciclo_id BIGINT UNSIGNED NOT NULL,
    codigo VARCHAR(80) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_encasetamiento DATE NULL,
    fecha_estimada_salida DATE NULL,
    fecha_cierre DATE NULL,
    edad_aves_dias INT UNSIGNED DEFAULT 0,
    linea_genetica VARCHAR(120) NULL,
    aves_recibidas INT UNSIGNED DEFAULT 0,
    aves_actuales INT UNSIGNED DEFAULT 0,
    mortalidad_acumulada INT UNSIGNED DEFAULT 0,
    estado ENUM('activo','cerrado','suspendido') NOT NULL DEFAULT 'activo',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ciclo_codigo (codigo),
    CONSTRAINT fk_ciclos_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_ciclos_galpones FOREIGN KEY (galpon_id) REFERENCES galpones(id),
    CONSTRAINT fk_ciclos_fases FOREIGN KEY (fase_ciclo_id) REFERENCES fases_ciclo(id)
) ENGINE=InnoDB;

CREATE TABLE estados_documentales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    color VARCHAR(30) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE tipos_formato (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    fundamento VARCHAR(255) NULL,
    frecuencia ENUM('diario','semanal','mensual','por_evento','por_ciclo') NOT NULL DEFAULT 'por_evento',
    permite_no_aplica TINYINT(1) NOT NULL DEFAULT 1,
    requiere_galpon TINYINT(1) NOT NULL DEFAULT 0,
    requiere_ciclo TINYINT(1) NOT NULL DEFAULT 0,
    requiere_firma TINYINT(1) NOT NULL DEFAULT 1,
    dias_maximo_atraso INT UNSIGNED NOT NULL DEFAULT 3,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE formatos_registros (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_formato_id BIGINT UNSIGNED NOT NULL,
    granja_id BIGINT UNSIGNED NOT NULL,
    galpon_id BIGINT UNSIGNED NULL,
    ciclo_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    estado_documental_id BIGINT UNSIGNED NOT NULL,
    codigo_consecutivo VARCHAR(100) NOT NULL UNIQUE,
    fecha_formato DATE NOT NULL,
    hora_formato TIME NULL,
    es_no_aplica TINYINT(1) NOT NULL DEFAULT 0,
    motivo_no_aplica TEXT NULL,
    datos_json JSON NOT NULL,
    observaciones TEXT NULL,
    hash_documento VARCHAR(128) NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    origen_registro ENUM('web','movil','offline') NOT NULL DEFAULT 'web',
    sincronizado TINYINT(1) NOT NULL DEFAULT 1,
    diligenciado_at DATETIME NULL,
    aprobado_at DATETIME NULL,
    cerrado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_registros_tipos FOREIGN KEY (tipo_formato_id) REFERENCES tipos_formato(id),
    CONSTRAINT fk_registros_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_registros_galpones FOREIGN KEY (galpon_id) REFERENCES galpones(id),
    CONSTRAINT fk_registros_ciclos FOREIGN KEY (ciclo_id) REFERENCES ciclos_productivos(id),
    CONSTRAINT fk_registros_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_registros_estados FOREIGN KEY (estado_documental_id) REFERENCES estados_documentales(id)
) ENGINE=InnoDB;

CREATE TABLE firmas_digitales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    formato_registro_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    firma_path VARCHAR(255) NULL,
    metodo ENUM('dibujo','password','usuario_autenticado') NOT NULL DEFAULT 'usuario_autenticado',
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    hash_firma VARCHAR(128) NULL,
    firmado_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_firmas_registros FOREIGN KEY (formato_registro_id) REFERENCES formatos_registros(id),
    CONSTRAINT fk_firmas_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE certificaciones_granja (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    granja_id BIGINT UNSIGNED NOT NULL,
    numero_certificacion VARCHAR(120) NULL,
    entidad VARCHAR(100) NOT NULL DEFAULT 'ICA',
    fecha_expedicion DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado ENUM('vigente','proxima_vencer','vencida','en_renovacion') NOT NULL DEFAULT 'vigente',
    documento_path VARCHAR(255) NULL,
    responsable_id BIGINT UNSIGNED NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cert_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_cert_usuarios FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE categorias_documentales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE documentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_documental_id BIGINT UNSIGNED NOT NULL,
    granja_id BIGINT UNSIGNED NULL,
    galpon_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    archivo_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NULL,
    extension VARCHAR(20) NULL,
    peso_bytes BIGINT UNSIGNED NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    estado ENUM('vigente','obsoleto','en_revision') NOT NULL DEFAULT 'vigente',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_docs_categorias FOREIGN KEY (categoria_documental_id) REFERENCES categorias_documentales(id),
    CONSTRAINT fk_docs_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_docs_galpones FOREIGN KEY (galpon_id) REFERENCES galpones(id),
    CONSTRAINT fk_docs_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE evidencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    formato_registro_id BIGINT UNSIGNED NULL,
    documento_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('foto','video','pdf','word','excel','otro') NOT NULL DEFAULT 'foto',
    titulo VARCHAR(180) NULL,
    archivo_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NULL,
    extension VARCHAR(20) NULL,
    peso_bytes BIGINT UNSIGNED NULL,
    latitud DECIMAL(10,8) NULL,
    longitud DECIMAL(11,8) NULL,
    tomada_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evidencias_registros FOREIGN KEY (formato_registro_id) REFERENCES formatos_registros(id),
    CONSTRAINT fk_evidencias_docs FOREIGN KEY (documento_id) REFERENCES documentos(id),
    CONSTRAINT fk_evidencias_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE alertas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    granja_id BIGINT UNSIGNED NULL,
    galpon_id BIGINT UNSIGNED NULL,
    formato_registro_id BIGINT UNSIGNED NULL,
    certificacion_id BIGINT UNSIGNED NULL,
    tipo VARCHAR(80) NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    estado ENUM('pendiente','enviada','leida','atendida','cerrada') NOT NULL DEFAULT 'pendiente',
    fecha_limite DATE NULL,
    enviada_at DATETIME NULL,
    leida_at DATETIME NULL,
    cerrada_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_alertas_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_alertas_granjas FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_alertas_galpones FOREIGN KEY (galpon_id) REFERENCES galpones(id),
    CONSTRAINT fk_alertas_registros FOREIGN KEY (formato_registro_id) REFERENCES formatos_registros(id),
    CONSTRAINT fk_alertas_cert FOREIGN KEY (certificacion_id) REFERENCES certificaciones_granja(id)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    modulo VARCHAR(100) NOT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(100) NULL,
    registro_id BIGINT UNSIGNED NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX idx_usuarios_email ON usuarios (email);
CREATE INDEX idx_granjas_sigla ON granjas (sigla);
CREATE INDEX idx_galpones_granja ON galpones (granja_id);
CREATE INDEX idx_ciclos_granja_galpon ON ciclos_productivos (granja_id, galpon_id);
CREATE INDEX idx_registros_granja_fecha ON formatos_registros (granja_id, fecha_formato);
CREATE INDEX idx_registros_estado ON formatos_registros (estado_documental_id);
CREATE INDEX idx_registros_tipo_fecha ON formatos_registros (tipo_formato_id, fecha_formato);
CREATE INDEX idx_certificaciones_vencimiento ON certificaciones_granja (fecha_vencimiento);
CREATE INDEX idx_alertas_estado_prioridad ON alertas (estado, prioridad);
CREATE INDEX idx_documentos_categoria ON documentos (categoria_documental_id);
CREATE INDEX idx_auditoria_modulo ON auditoria (modulo, accion);

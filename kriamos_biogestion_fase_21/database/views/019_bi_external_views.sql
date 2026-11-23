USE kriamos_biogestion_ica;

CREATE OR REPLACE VIEW vw_bi_granjas AS
SELECT
    id,
    sigla,
    nombre,
    capacidad_aves,
    total_galpones,
    estado
FROM granjas;

CREATE OR REPLACE VIEW vw_bi_galpones AS
SELECT
    ga.id,
    g.sigla AS granja,
    ga.codigo AS galpon,
    ga.capacidad_aves,
    ga.estado
FROM galpones ga
INNER JOIN granjas g ON g.id = ga.granja_id;

CREATE OR REPLACE VIEW vw_bi_formatos AS
SELECT
    fr.id,
    fr.codigo_consecutivo,
    g.sigla AS granja,
    ga.codigo AS galpon,
    c.codigo AS ciclo,
    tf.codigo AS tipo_formato_codigo,
    tf.nombre AS tipo_formato,
    ed.nombre AS estado,
    fr.fecha_formato,
    fr.es_no_aplica,
    fr.created_at,
    u.email AS usuario
FROM formatos_registros fr
INNER JOIN granjas g ON g.id = fr.granja_id
LEFT JOIN galpones ga ON ga.id = fr.galpon_id
LEFT JOIN ciclos_productivos c ON c.id = fr.ciclo_id
INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
INNER JOIN usuarios u ON u.id = fr.usuario_id;

CREATE OR REPLACE VIEW vw_bi_alertas AS
SELECT
    a.id,
    a.tipo,
    a.prioridad,
    a.estado,
    a.titulo,
    g.sigla AS granja,
    ga.codigo AS galpon,
    a.fecha_limite,
    a.created_at
FROM alertas a
LEFT JOIN granjas g ON g.id = a.granja_id
LEFT JOIN galpones ga ON ga.id = a.galpon_id;

CREATE OR REPLACE VIEW vw_bi_certificaciones AS
SELECT
    c.id,
    g.sigla AS granja,
    c.numero_certificacion,
    c.entidad,
    c.fecha_expedicion,
    c.fecha_vencimiento,
    DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_para_vencer,
    c.estado
FROM certificaciones_granja c
INNER JOIN granjas g ON g.id = c.granja_id;

CREATE OR REPLACE VIEW vw_bi_documentos AS
SELECT
    d.id,
    d.titulo,
    cd.nombre AS categoria,
    g.sigla AS granja,
    ga.codigo AS galpon,
    d.extension,
    d.version,
    d.estado,
    d.created_at,
    d.updated_at
FROM documentos d
INNER JOIN categorias_documentales cd ON cd.id = d.categoria_documental_id
LEFT JOIN granjas g ON g.id = d.granja_id
LEFT JOIN galpones ga ON ga.id = d.galpon_id;

CREATE OR REPLACE VIEW vw_bi_indicadores_granja AS
SELECT
    g.sigla AS granja,
    g.nombre,
    COUNT(fr.id) AS total_formatos,
    SUM(CASE WHEN ed.codigo IN ('APROBADO','CERRADO') THEN 1 ELSE 0 END) AS cerrados,
    SUM(CASE WHEN ed.codigo IN ('DILIGENCIADO','EN_REVISION','PENDIENTE_FIRMA') THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN fr.es_no_aplica = 1 THEN 1 ELSE 0 END) AS no_aplica,
    SUM(CASE WHEN fr.fecha_formato <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
             AND ed.codigo NOT IN ('APROBADO','CERRADO','NO_APLICA') THEN 1 ELSE 0 END) AS atrasados,
    ROUND(
        CASE WHEN COUNT(fr.id) > 0
        THEN (SUM(CASE WHEN ed.codigo IN ('APROBADO','CERRADO') THEN 1 ELSE 0 END) / COUNT(fr.id)) * 100
        ELSE 0 END, 2
    ) AS tasa_complecion
FROM granjas g
LEFT JOIN formatos_registros fr ON fr.granja_id = g.id
LEFT JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
GROUP BY g.id;

CREATE OR REPLACE VIEW vw_bi_mortalidad_diaria AS
SELECT
    g.sigla AS granja,
    ga.codigo AS galpon,
    fr.fecha_formato AS fecha,
    SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(fr.datos_json, '$.aves_muertas')) AS UNSIGNED)) AS aves_muertas
FROM formatos_registros fr
INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
INNER JOIN granjas g ON g.id = fr.granja_id
LEFT JOIN galpones ga ON ga.id = fr.galpon_id
WHERE tf.codigo = 'MORTALIDAD_DIARIA'
GROUP BY g.id, ga.id, fr.fecha_formato;

CREATE OR REPLACE VIEW vw_bi_uso_sistema AS
SELECT
    DATE(a.created_at) AS fecha,
    a.modulo,
    a.accion,
    COUNT(*) AS total_acciones,
    COUNT(DISTINCT a.usuario_id) AS usuarios_activos
FROM auditoria a
GROUP BY DATE(a.created_at), a.modulo, a.accion;

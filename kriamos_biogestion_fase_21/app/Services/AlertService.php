<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Certificacion;
use App\Models\Usuario;
use App\Services\Database;

class AlertService
{
    private array $config;
    private Alerta $alerta;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/alerts.php';
        $this->alerta = new Alerta();
    }

    public function generarTodas(): array
    {
        return [
            'formatos_atrasados' => $this->generarFormatosAtrasados(),
            'certificaciones' => $this->generarCertificaciones(),
            'acciones_correctivas' => $this->generarAccionesCorrectivas(),
            'incidencias_criticas' => $this->generarIncidenciasCriticas(),
            'productos_vencidos' => $this->generarProductosVencidos(),
        ];
    }

    public function generarFormatosAtrasados(): int
    {
        $db = Database::connection();
        $dias = (int) $this->config['format_delay_days'];

        $stmt = $db->prepare("
            SELECT fr.*, tf.nombre AS tipo_nombre, tf.codigo AS tipo_codigo,
                   g.sigla AS granja_sigla, g.nombre AS granja_nombre,
                   ga.codigo AS galpon_codigo,
                   u.id AS usuario_id
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            LEFT JOIN galpones ga ON ga.id = fr.galpon_id
            INNER JOIN usuarios u ON u.id = fr.usuario_id
            INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            WHERE fr.fecha_formato <= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
              AND ed.codigo NOT IN ('APROBADO','CERRADO','NO_APLICA')
        ");
        $stmt->bindValue('dias', $dias, \PDO::PARAM_INT);
        $stmt->execute();

        $count = 0;
        foreach ($stmt->fetchAll() as $row) {
            $id = $this->alerta->createIfNotExists([
                'usuario_id' => $row['usuario_id'],
                'granja_id' => $row['granja_id'],
                'galpon_id' => $row['galpon_id'],
                'formato_registro_id' => $row['id'],
                'tipo' => 'formato_atrasado',
                'titulo' => 'Formato atrasado: ' . $row['tipo_nombre'],
                'mensaje' => 'El formato ' . $row['codigo_consecutivo'] . ' de ' . $row['granja_sigla'] . ' supera el plazo máximo de ' . $dias . ' días sin cierre/aprobación.',
                'prioridad' => 'alta',
                'fecha_limite' => $row['fecha_formato'],
            ]);

            if ($id) {
                $count++;
            }
        }

        return $count;
    }

    public function generarCertificaciones(): int
    {
        $days = $this->config['certification_days'];
        $certificaciones = (new Certificacion())->candidatesForAlerts($days);
        $responsable = (new Usuario())->firstByRoleNames(['Administrador del sistema', 'Gerencia / Dirección']);
        $count = 0;

        foreach ($certificaciones as $cert) {
            $vence = new \DateTime($cert['fecha_vencimiento']);
            $hoy = new \DateTime(date('Y-m-d'));
            $diff = (int) $hoy->diff($vence)->format('%r%a');

            $prioridad = 'media';
            if ($diff <= 15) {
                $prioridad = 'critica';
            } elseif ($diff <= 30) {
                $prioridad = 'alta';
            }

            $titulo = $diff < 0
                ? 'Certificación vencida: ' . $cert['granja_sigla']
                : 'Certificación próxima a vencer: ' . $cert['granja_sigla'];

            $mensaje = $diff < 0
                ? 'La certificación de la granja ' . $cert['granja_nombre'] . ' venció el ' . $cert['fecha_vencimiento'] . '.'
                : 'La certificación de la granja ' . $cert['granja_nombre'] . ' vence en ' . $diff . ' días, fecha: ' . $cert['fecha_vencimiento'] . '.';

            $id = $this->alerta->createIfNotExists([
                'usuario_id' => $cert['responsable_id'] ?? ($responsable['id'] ?? null),
                'granja_id' => $cert['granja_id'],
                'certificacion_id' => $cert['id'],
                'tipo' => 'certificacion_vencimiento',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'prioridad' => $prioridad,
                'fecha_limite' => $cert['fecha_vencimiento'],
            ]);

            if ($id) {
                $count++;
            }
        }

        return $count;
    }

    public function generarAccionesCorrectivas(): int
    {
        $db = Database::connection();

        $stmt = $db->query("
            SELECT fr.*, g.sigla AS granja_sigla, tf.nombre AS tipo_nombre
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            WHERE tf.codigo = 'HALLAZGOS_ACCIONES'
        ");

        $count = 0;

        foreach ($stmt->fetchAll() as $row) {
            $datos = json_decode($row['datos_json'], true) ?: [];
            $fechaLimite = $datos['fecha_limite'] ?? null;
            $estadoAccion = strtolower($datos['estado_accion'] ?? '');

            if ($fechaLimite && $fechaLimite < date('Y-m-d') && $estadoAccion !== 'cerrada') {
                $id = $this->alerta->createIfNotExists([
                    'usuario_id' => $row['usuario_id'],
                    'granja_id' => $row['granja_id'],
                    'galpon_id' => $row['galpon_id'],
                    'formato_registro_id' => $row['id'],
                    'tipo' => 'accion_correctiva_vencida',
                    'titulo' => 'Acción correctiva vencida',
                    'mensaje' => 'La acción correctiva registrada en ' . $row['codigo_consecutivo'] . ' venció el ' . $fechaLimite . '.',
                    'prioridad' => 'alta',
                    'fecha_limite' => $fechaLimite,
                ]);

                if ($id) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function generarIncidenciasCriticas(): int
    {
        $db = Database::connection();

        $stmt = $db->query("
            SELECT i.*, fr.usuario_id, fr.granja_id, fr.galpon_id, fr.codigo_consecutivo
            FROM incidencias_calidad i
            INNER JOIN formatos_registros fr ON fr.id = i.formato_registro_id
            WHERE i.estado = 'abierta'
              AND i.nivel IN ('alta','critica')
        ");

        $count = 0;

        foreach ($stmt->fetchAll() as $row) {
            $id = $this->alerta->createIfNotExists([
                'usuario_id' => $row['usuario_id'],
                'granja_id' => $row['granja_id'],
                'galpon_id' => $row['galpon_id'],
                'formato_registro_id' => $row['formato_registro_id'],
                'tipo' => 'incidencia_calidad',
                'titulo' => 'Incidencia de calidad: ' . $row['nivel'],
                'mensaje' => 'El registro ' . $row['codigo_consecutivo'] . ' presenta una incidencia: ' . $row['mensaje'],
                'prioridad' => $row['nivel'] === 'critica' ? 'critica' : 'alta',
                'fecha_limite' => date('Y-m-d'),
            ]);

            if ($id) {
                $count++;
            }
        }

        return $count;
    }

    public function generarProductosVencidos(): int
    {
        $db = Database::connection();

        $stmt = $db->query("
            SELECT fr.*, tf.nombre AS tipo_nombre, g.sigla AS granja_sigla
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            WHERE tf.codigo IN ('VACUNACION','MEDICAMENTOS_BIOLOGICOS')
        ");

        $count = 0;

        foreach ($stmt->fetchAll() as $row) {
            $datos = json_decode($row['datos_json'], true) ?: [];
            $fechaVencimiento = $datos['fecha_vencimiento'] ?? null;

            if ($fechaVencimiento && $fechaVencimiento < date('Y-m-d')) {
                $id = $this->alerta->createIfNotExists([
                    'usuario_id' => $row['usuario_id'],
                    'granja_id' => $row['granja_id'],
                    'galpon_id' => $row['galpon_id'],
                    'formato_registro_id' => $row['id'],
                    'tipo' => 'producto_vencido',
                    'titulo' => 'Producto vencido registrado',
                    'mensaje' => 'El formato ' . $row['codigo_consecutivo'] . ' registra un producto con fecha de vencimiento ' . $fechaVencimiento . '.',
                    'prioridad' => 'critica',
                    'fecha_limite' => $fechaVencimiento,
                ]);

                if ($id) {
                    $count++;
                }
            }
        }

        return $count;
    }
}

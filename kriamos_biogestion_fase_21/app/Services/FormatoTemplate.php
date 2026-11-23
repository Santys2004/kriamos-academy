<?php

namespace App\Services;

class FormatoTemplate
{
    public static function fields(string $codigo): array
    {
        $templates = self::templates();
        return $templates[$codigo]['fields'] ?? $templates['DEFAULT']['fields'];
    }

    public static function guide(string $codigo): array
    {
        $templates = self::templates();
        return [
            'titulo' => $templates[$codigo]['titulo'] ?? 'Guía general de diligenciamiento',
            'resumen' => $templates[$codigo]['resumen'] ?? 'Diligencie la información completa, real y verificable según el POE interno de Kriamos.',
            'items' => $templates[$codigo]['items'] ?? [
                'Verifique la granja, galpón y ciclo antes de guardar.',
                'No deje campos obligatorios vacíos.',
                'Use la opción No aplica únicamente cuando corresponda a la fase del ciclo.',
                'Registre observaciones claras y verificables.'
            ],
        ];
    }

    public static function normalizePostData(array $post, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $value = $post[$name] ?? null;

            if (($field['type'] ?? 'text') === 'number') {
                $data[$name] = $value === '' || $value === null ? null : (float) $value;
            } else {
                $data[$name] = is_string($value) ? trim($value) : $value;
            }
        }

        return $data;
    }

    public static function templates(): array
    {
        return [
            'ING_PERSONAS_VEHICULOS' => [
                'titulo' => 'Ingreso de personas, vehículos y objetos',
                'resumen' => 'Este formato controla el ingreso a la granja y permite evidenciar medidas de bioseguridad.',
                'items' => [
                    'Registre fecha, hora, procedencia y motivo del ingreso.',
                    'Verifique si aplica desinfección del vehículo.',
                    'Registre placa cuando ingrese vehículo.',
                    'La declaración sanitaria debe ser real y verificable.',
                ],
                'fields' => [
                    ['name'=>'hora_ingreso','label'=>'Hora de ingreso','type'=>'time','required'=>true],
                    ['name'=>'nombre_visitante','label'=>'Nombre completo','type'=>'text','required'=>true],
                    ['name'=>'documento_identidad','label'=>'Documento de identidad','type'=>'text','required'=>true],
                    ['name'=>'empresa_dependencia','label'=>'Empresa o dependencia','type'=>'text','required'=>false],
                    ['name'=>'cargo','label'=>'Cargo','type'=>'text','required'=>false],
                    ['name'=>'motivo_ingreso','label'=>'Motivo del ingreso','type'=>'textarea','required'=>true],
                    ['name'=>'procedencia','label'=>'Procedencia','type'=>'text','required'=>true],
                    ['name'=>'placa_vehiculo','label'=>'Placa del vehículo','type'=>'text','required'=>false],
                    ['name'=>'desinfeccion_realizada','label'=>'Desinfección realizada','type'=>'select','required'=>true,'options'=>['Sí','No','No aplica']],
                    ['name'=>'elementos_ingresados','label'=>'Elementos ingresados','type'=>'textarea','required'=>false],
                    ['name'=>'declaracion_sanitaria','label'=>'Declaración sanitaria','type'=>'textarea','required'=>true],
                    ['name'=>'responsable_autoriza','label'=>'Responsable que autoriza','type'=>'text','required'=>true],
                ],
            ],
            'DESINFECCION_VEHICULOS' => [
                'titulo'=>'Desinfección de vehículos',
                'resumen'=>'Permite registrar producto, concentración, tiempo de contacto y responsable de la desinfección.',
                'items'=>['Registre placa y tipo de vehículo.', 'Indique producto y concentración utilizada.', 'El tiempo de contacto debe coincidir con el POE interno.'],
                'fields'=>[
                    ['name'=>'hora','label'=>'Hora','type'=>'time','required'=>true],
                    ['name'=>'placa','label'=>'Placa','type'=>'text','required'=>true],
                    ['name'=>'tipo_vehiculo','label'=>'Tipo de vehículo','type'=>'text','required'=>true],
                    ['name'=>'motivo_ingreso','label'=>'Motivo de ingreso','type'=>'textarea','required'=>true],
                    ['name'=>'producto_utilizado','label'=>'Producto utilizado','type'=>'text','required'=>true],
                    ['name'=>'concentracion','label'=>'Concentración','type'=>'text','required'=>true],
                    ['name'=>'tiempo_contacto','label'=>'Tiempo de contacto','type'=>'text','required'=>true],
                    ['name'=>'verificacion_visual','label'=>'Verificación visual','type'=>'select','required'=>true,'options'=>['Conforme','No conforme']],
                    ['name'=>'responsable','label'=>'Responsable de aplicación','type'=>'text','required'=>true],
                ],
            ],
            'TRATAMIENTO_AGUA' => [
                'titulo'=>'Tratamiento y calidad del agua',
                'resumen'=>'Registra el control de tratamiento de agua por granja, galpón y ciclo.',
                'items'=>['Registre fuente de agua.', 'Controle pH y cloro residual.', 'Anote el producto y dosificación utilizada.'],
                'fields'=>[
                    ['name'=>'fuente_agua','label'=>'Fuente de agua','type'=>'text','required'=>true],
                    ['name'=>'producto_utilizado','label'=>'Producto utilizado','type'=>'text','required'=>true],
                    ['name'=>'dosificacion','label'=>'Dosificación','type'=>'text','required'=>true],
                    ['name'=>'ph','label'=>'pH','type'=>'number','required'=>true],
                    ['name'=>'cloro_residual','label'=>'Cloro residual','type'=>'text','required'=>true],
                    ['name'=>'resultado_verificacion','label'=>'Resultado de verificación','type'=>'select','required'=>true,'options'=>['Conforme','No conforme']],
                    ['name'=>'responsable','label'=>'Responsable','type'=>'text','required'=>true],
                ],
            ],
            'MORTALIDAD_DIARIA' => [
                'titulo'=>'Mortalidad diaria',
                'resumen'=>'Registra mortalidad diaria por galpón y permite alimentar indicadores sanitarios.',
                'items'=>['No registre números negativos.', 'Compare mortalidad con aves actuales.', 'Registre causa probable y método de disposición.'],
                'fields'=>[
                    ['name'=>'edad_lote','label'=>'Edad del lote en días','type'=>'number','required'=>true],
                    ['name'=>'aves_muertas','label'=>'Número de aves muertas','type'=>'number','required'=>true],
                    ['name'=>'causa_probable','label'=>'Causa probable','type'=>'textarea','required'=>true],
                    ['name'=>'metodo_disposicion','label'=>'Método de disposición','type'=>'text','required'=>true],
                    ['name'=>'responsable','label'=>'Responsable','type'=>'text','required'=>true],
                ],
            ],
            'VACUNACION' => [
                'titulo'=>'Programa sanitario y vacunación',
                'resumen'=>'Registra vacunas aplicadas, lote, laboratorio, vía, dosis y responsable sanitario.',
                'items'=>['Verifique edad del ave y programa sanitario.', 'Registre lote y fecha de vencimiento del biológico.', 'Indique vía de administración y número de aves vacunadas.'],
                'fields'=>[
                    ['name'=>'edad_ave','label'=>'Edad del ave','type'=>'number','required'=>true],
                    ['name'=>'enfermedad_prevenir','label'=>'Enfermedad a prevenir','type'=>'text','required'=>true],
                    ['name'=>'vacuna_utilizada','label'=>'Vacuna utilizada','type'=>'text','required'=>true],
                    ['name'=>'laboratorio','label'=>'Laboratorio','type'=>'text','required'=>true],
                    ['name'=>'lote_producto','label'=>'Lote del producto','type'=>'text','required'=>true],
                    ['name'=>'fecha_vencimiento','label'=>'Fecha de vencimiento','type'=>'date','required'=>true],
                    ['name'=>'dosis','label'=>'Dosis','type'=>'text','required'=>true],
                    ['name'=>'via_administracion','label'=>'Vía de administración','type'=>'text','required'=>true],
                    ['name'=>'aves_vacunadas','label'=>'Número de aves vacunadas','type'=>'number','required'=>true],
                    ['name'=>'medico_veterinario','label'=>'Médico veterinario responsable','type'=>'text','required'=>true],
                ],
            ],
            'MEDICAMENTOS_BIOLOGICOS' => [
                'titulo'=>'Uso de medicamentos, biológicos y plaguicidas',
                'resumen'=>'Registra productos usados, dosis, motivo, responsable y tiempo de retiro.',
                'items'=>['Verifique que el producto no esté vencido.', 'Registre principio activo y lote.', 'Indique tiempo de retiro cuando aplique.'],
                'fields'=>[
                    ['name'=>'producto','label'=>'Producto','type'=>'text','required'=>true],
                    ['name'=>'tipo_producto','label'=>'Tipo de producto','type'=>'select','required'=>true,'options'=>['Medicamento','Biológico','Plaguicida','Otro']],
                    ['name'=>'principio_activo','label'=>'Principio activo','type'=>'text','required'=>true],
                    ['name'=>'lote','label'=>'Lote','type'=>'text','required'=>true],
                    ['name'=>'fecha_vencimiento','label'=>'Fecha de vencimiento','type'=>'date','required'=>true],
                    ['name'=>'dosis','label'=>'Dosis','type'=>'text','required'=>true],
                    ['name'=>'via_administracion','label'=>'Vía de administración','type'=>'text','required'=>false],
                    ['name'=>'motivo_uso','label'=>'Motivo de uso','type'=>'textarea','required'=>true],
                    ['name'=>'tiempo_retiro','label'=>'Tiempo de retiro','type'=>'text','required'=>false],
                    ['name'=>'responsable_aplicacion','label'=>'Responsable de aplicación','type'=>'text','required'=>true],
                ],
            ],
            'HALLAZGOS_ACCIONES' => [
                'titulo'=>'Observaciones, hallazgos y acciones correctivas',
                'resumen'=>'Permite gestionar hallazgos, responsables, fechas límite y cierre.',
                'items'=>['Describa el hallazgo con claridad.', 'Asigne responsable y fecha límite.', 'Clasifique la criticidad.'],
                'fields'=>[
                    ['name'=>'hallazgo','label'=>'Hallazgo','type'=>'textarea','required'=>true],
                    ['name'=>'tipo_hallazgo','label'=>'Tipo de hallazgo','type'=>'text','required'=>true],
                    ['name'=>'criticidad','label'=>'Criticidad','type'=>'select','required'=>true,'options'=>['Baja','Media','Alta','Crítica']],
                    ['name'=>'accion_correctiva','label'=>'Acción correctiva','type'=>'textarea','required'=>true],
                    ['name'=>'responsable','label'=>'Responsable','type'=>'text','required'=>true],
                    ['name'=>'fecha_limite','label'=>'Fecha límite','type'=>'date','required'=>true],
                    ['name'=>'estado_accion','label'=>'Estado de la acción','type'=>'select','required'=>true,'options'=>['Abierta','En proceso','Cerrada']],
                ],
            ],
            'DEFAULT' => [
                'fields' => [
                    ['name'=>'actividad_realizada','label'=>'Actividad realizada','type'=>'textarea','required'=>true],
                    ['name'=>'producto_utilizado','label'=>'Producto utilizado / insumo','type'=>'text','required'=>false],
                    ['name'=>'cantidad_dosis','label'=>'Cantidad / dosis','type'=>'text','required'=>false],
                    ['name'=>'resultado','label'=>'Resultado','type'=>'select','required'=>true,'options'=>['Conforme','No conforme','No aplica']],
                    ['name'=>'responsable','label'=>'Responsable','type'=>'text','required'=>true],
                    ['name'=>'hallazgos','label'=>'Hallazgos u observaciones','type'=>'textarea','required'=>false],
                    ['name'=>'accion_correctiva','label'=>'Acción correctiva si aplica','type'=>'textarea','required'=>false],
                ],
            ],
        ];
    }
}

<?php

require_once dirname(__DIR__, 2) . '/config/env.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__, 2) . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$datasetModel = new App\Models\BiExternalDataset();
$query = new App\Services\BIExternal\BiDatasetQueryService();
$csv = new App\Services\BIExternal\BiCsvExportService();

$result = [];

foreach ($datasetModel->active() as $dataset) {
    $data = $query->query($dataset['codigo'], ['limit' => 10000]);

    if (!$data['ok']) {
        $result[$dataset['codigo']] = 'ERROR: ' . $data['message'];
        continue;
    }

    $path = $csv->saveCsv($dataset['codigo'], $data['rows']);
    $datasetModel->updateExportInfo((int) $dataset['id'], $path, count($data['rows']));

    $result[$dataset['codigo']] = [
        'path' => $path,
        'rows' => count($data['rows']),
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit(0);

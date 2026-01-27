<?php

namespace App\Controllers;

use App\Models\EmployeeImportModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Employee Import Controller
 * POST /api/import-employee?overwrite=1&verbose=1
 */
class EmployeeImportController extends ResourceController
{
    protected $modelName = 'App\Models\EmployeeImportModel';
    protected $format = 'json';

    public function import()
    {
        $overwrite = $this->request->getGet('overwrite') === '1' || 
                     strtolower($this->request->getGet('overwrite')) === 'true';
        
        $verbose = $this->request->getGet('verbose') === '1' || 
                   strtolower($this->request->getGet('verbose')) === 'true';

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return $this->fail('Missing or invalid file upload', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($file->getExtension() !== 'xlsx') {
            return $this->fail('Only .xlsx files are allowed', ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $model = new EmployeeImportModel();
            $result = $model->importFromExcel($file->getTempName(), $overwrite, $verbose);

            return $this->respond([
                'success' => true,
                'message' => 'Import completed successfully',
                'data' => [
                    'inserted' => $result['inserted'],
                    'updated' => $result['updated'],
                    'skipped' => $result['skipped']
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Import failed: ' . $e->getMessage());
            return $this->fail('Import error: ' . $e->getMessage(), ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
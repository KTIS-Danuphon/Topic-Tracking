<?php
class SecureFileUpload
{
    protected string $uploadPath;
    protected array $errors = [];

    protected int $maxSize = 10485760; // 10MB

    // mapping ext => mime ที่อนุญาต
    protected array $allowMap = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'pdf'  => [
            'application/pdf',
            'application/x-pdf',
            'application/octet-stream' // Windows บางเครื่อง
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]
    ];

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = rtrim($uploadPath, '/') . '/';

        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0755, true)) {
                throw new Exception("Cannot create upload directory");
            }
        }

        $this->createHtaccess();
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, int $taskID): array
    {
        $uploaded = [];

        if (empty($files['name'][0])) {
            return [];
        }

        $taskDir = $this->uploadPath . 'task_' . $taskID . '/';
        if (!is_dir($taskDir)) {
            mkdir($taskDir, 0755, true);
        }

        foreach ($files['name'] as $i => $originalName) {

            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $this->errors[] = "UPLOAD_ERROR: {$originalName}";
                continue;
            }

            if ($files['size'][$i] > $this->maxSize) {
                $this->errors[] = "FILE_TOO_LARGE: {$originalName}";
                continue;
            }

            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!isset($this->allowMap[$ext])) {
                $this->errors[] = "EXT_NOT_ALLOWED: {$originalName}";
                continue;
            }

            // ตรวจ MIME จริง
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $files['tmp_name'][$i]);
            finfo_close($finfo);

            if (!in_array($mime, $this->allowMap[$ext])) {
                $this->errors[] = "MIME_NOT_MATCH: {$originalName} ({$mime})";
                continue;
            }

            // ตั้งชื่อไฟล์ใหม่
            $savedName  = hash('sha256', uniqid($taskID, true)) . '.' . $ext;
            $targetPath = $taskDir . $savedName;

            if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $this->errors[] = "MOVE_FAILED: {$originalName}";
                continue;
            }

            $uploaded[] = [
                'original_name' => $originalName,
                'saved_name'    => $savedName,
                'file_path'     => $taskDir,
                'file_size'     => $files['size'][$i],
                'file_type'     => $mime,
                'extension'     => $ext
            ];
        }

        return $uploaded;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function createHtaccess(): void
    {
        $htaccess = $this->uploadPath . '.htaccess';

        if (!file_exists($htaccess)) {
            file_put_contents(
                $htaccess,
                <<<HT
Options -Indexes
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phar)$">
    Deny from all
</FilesMatch>
HT
            );
        }
    }
}

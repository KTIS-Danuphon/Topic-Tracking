<?php
class SecureFileUpload
{
    protected string $uploadPath;
    protected array $errors = [];

    protected array $allowExt = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];
    protected int $maxSize = 10485760; // 10MB

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

        // แยกโฟลเดอร์ตาม task
        $taskDir = $this->uploadPath . 'task_' . $taskID . '/';
        if (!is_dir($taskDir)) {
            mkdir($taskDir, 0755, true);
        }

        foreach ($files['name'] as $i => $originalName) {

            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $this->errors[] = "Upload error: {$originalName}";
                continue;
            }

            if ($files['size'][$i] > $this->maxSize) {
                $this->errors[] = "File too large: {$originalName}";
                continue;
            }

            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($ext, $this->allowExt)) {
                $this->errors[] = "File type not allowed: {$originalName}";
                continue;
            }

            // ตรวจ MIME จริง
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['tmp_name'][$i]);
            finfo_close($finfo);

            if (!preg_match('/(image|pdf|officedocument|spreadsheet)/', $mime)) {
                $this->errors[] = "Invalid file content: {$originalName}";
                continue;
            }

            // ตั้งชื่อไฟล์ใหม่
            $savedName = hash('sha256', uniqid($taskID, true)) . '.' . $ext;
            $targetPath = $taskDir . $savedName;

            if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {

                $uploaded[] = [
                    'original_name' => $originalName,
                    'saved_name' => $savedName,
                    'file_path' => $targetPath,
                    'file_size' => $files['size'][$i],
                    'file_type' => $mime,
                    'extension' => $ext
                ];
            } else {
                $this->errors[] = "Cannot move file: {$originalName}";
            }
        }

        return $uploaded;
    }

    /**
     * Get upload errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create .htaccess to block script execution
     */
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

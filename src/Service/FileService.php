<?php


namespace App\Service;


use App\Dto\ETDFile;
use App\Entity\ETD;
use App\Entity\User;
use DateTime;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileService
{
    private const FOLDER_PATH = '../files';

    public function importFiles($etd, $files): bool
    {
        $path = $this->getFolderPath($etd);
        if(!is_dir($path)) {
            $permission = fileperms($this->getRootFolderPath());
            if(!$this->createPath($path, $permission)) {
                throw new FileException('Folder cannot be created');
            }
        }
        foreach ($files as $file) {
            try {
                $file->move($path, $file->getClientOriginalName());
            } catch (FileException $e) {
                throw new FileException('Unable to upload the file : ' . $file->getClientOriginalName());
            }
        }
        return true;
    }

    public function downloadFile($etd, $fileName)
    {
        $path = $this->getFolderPath($etd) . $fileName;
        if(!file_exists($path)) {
            return null;
        }
        return file_get_contents($path);
    }

    public function deleteFile($etd, $fileName): ?bool
    {
        $path = $this->getFolderPath($etd) . $fileName;
        if(!file_exists($path)) {
            return null;
        }
        return unlink($path);
    }

    /**
     * @throws \Exception
     */
    public function getETDFiles(ETD $etd): array
    {
        $files = [];
        $path = $this->getFolderPath($etd);
        if(!is_dir($path)) {
            return $files;
        }

        $fileNames = array_slice(scandir($path), 2);
        foreach ($fileNames as $fileName) {
            $file = new ETDFile();
            $file->setName($fileName);
            $dt = new DateTime();
            $dt->setTimestamp(filectime($path . $fileName));
            $file->setModificationDate($dt);
            $files[] = $file;
        }
        return $files;
    }

    #region Tools

    private function createPath(string $path, $permission): bool
    {
        if (!mkdir($path, $permission, true) || !is_dir($path)) {
            throw new FileException(sprintf('Directory "%s" was not created', $path));
        }
        return true;
    }

    private function getRootFolderPath(): string
    {
        return self::FOLDER_PATH . '/';
    }

    public function getFolderPath(ETD $etd): string
    {
        return $this->getRootFolderPath() . $etd->getVendor()->getNo() . '/' . $etd->getEtdDate()->format('Y-m-d') . '/';
    }

    #endregion
}
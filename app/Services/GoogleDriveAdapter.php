<?php

namespace App\Services;

use Google\Service\Drive as GoogleDrive;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

class GoogleDriveAdapter implements FilesystemAdapter
{
    protected GoogleDrive $service;
    protected ?string $rootFolderId;

    public function __construct(GoogleDrive $service, ?string $rootFolderId = null)
    {
        $this->service = $service;
        $this->rootFolderId = $rootFolderId ?: 'root';
    }

    protected function splitPath(string $path): array
    {
        $path = trim($path, '/');
        $parts = $path === '' ? [] : explode('/', $path);

        return $parts;
    }

    protected function findOrCreateFolder(string $name, string $parentId): string
    {
        $escaped = str_replace(["'", '\\'], ["\\'", '\\\\'], $name);
        $query = "name = '{$escaped}' and '{$parentId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false";

        $existing = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]);

        if (count($existing->getFiles()) > 0) {
            return $existing->getFiles()[0]->getId();
        }

        $folder = new \Google\Service\Drive\DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        $created = $this->service->files->create($folder, ['fields' => 'id']);

        return $created->getId();
    }

    protected function resolveParentId(string $path): string
    {
        $parentId = $this->rootFolderId;
        $parts = $this->splitPath(dirname($path) === '.' ? '' : dirname($path));

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $parentId = $this->findOrCreateFolder($part, $parentId);
        }

        return $parentId;
    }

    protected function findFileId(string $path): ?string
    {
        $parentId = $this->resolveParentId($path);
        $name = basename($path);

        if ($name === '' || $name === '.') {
            return $parentId;
        }

        $escaped = str_replace(["'", '\\'], ["\\'", '\\\\'], $name);
        $query = "name = '{$escaped}' and '{$parentId}' in parents and trashed = false";

        $files = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name, size, mimeType, modifiedTime)',
        ]);

        if (count($files->getFiles()) > 0) {
            return $files->getFiles()[0]->getId();
        }

        return null;
    }

    public function fileExists(string $path): bool
    {
        return $this->findFileId($path) !== null;
    }

    public function directoryExists(string $path): bool
    {
        return $this->findFileId($path) !== null;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $parentId = $this->resolveParentId($path);
        $name = basename($path);

        $file = new \Google\Service\Drive\DriveFile([
            'name' => $name,
            'parents' => [$parentId],
        ]);

        try {
            $this->service->files->create($file, [
                'data' => $contents,
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    public function read(string $path): string
    {
        $fileId = $this->findFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        try {
            $response = $this->service->files->get($fileId, ['alt' => 'media']);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function readStream(string $path)
    {
        $contents = $this->read($path);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        $fileId = $this->findFileId($path);

        if ($fileId !== null) {
            $this->service->files->delete($fileId);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $this->delete($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->resolveParentId($path . '/.keep');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        //
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        $fileId = $this->findFileId($path);
        $file = $this->service->files->get($fileId, ['fields' => 'mimeType']);

        return new FileAttributes($path, null, null, null, $file->getMimeType());
    }

    public function lastModified(string $path): FileAttributes
    {
        $fileId = $this->findFileId($path);
        $file = $this->service->files->get($fileId, ['fields' => 'modifiedTime']);

        return new FileAttributes(
            $path,
            null,
            null,
            strtotime($file->getModifiedTime())
        );
    }

    public function fileSize(string $path): FileAttributes
    {
        $fileId = $this->findFileId($path);
        $file = $this->service->files->get($fileId, ['fields' => 'size']);

        return new FileAttributes($path, (int) $file->getSize());
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $parentId = $this->findFileId($path) ?? $this->rootFolderId;

        $files = $this->service->files->listFiles([
            'q' => "'{$parentId}' in parents and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name, size, mimeType, modifiedTime)',
        ]);

        foreach ($files->getFiles() as $file) {
            $isDir = $file->getMimeType() === 'application/vnd.google-apps.folder';

            yield $isDir
                ? new DirectoryAttributes(trim($path . '/' . $file->getName(), '/'))
                : new FileAttributes(
                    trim($path . '/' . $file->getName(), '/'),
                    (int) $file->getSize(),
                    null,
                    strtotime($file->getModifiedTime())
                );

            if ($isDir && $deep) {
                yield from $this->listContents(trim($path . '/' . $file->getName(), '/'), $deep);
            }
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $fileId = $this->findFileId($source);
        $parentId = $this->resolveParentId($destination);

        if ($fileId !== null) {
            $this->service->files->update($fileId, new \Google\Service\Drive\DriveFile([
                'name' => basename($destination),
            ]), ['addParents' => $parentId]);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $fileId = $this->findFileId($source);
        $parentId = $this->resolveParentId($destination);

        if ($fileId !== null) {
            $this->service->files->copy($fileId, new \Google\Service\Drive\DriveFile([
                'name' => basename($destination),
                'parents' => [$parentId],
            ]));
        }
    }
}

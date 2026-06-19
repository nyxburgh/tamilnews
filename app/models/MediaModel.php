<?php
namespace App\Models;

use App\Core\Model;

class MediaModel extends Model
{
    protected string $table = 'tn_media';

    public function allPaginated(int $page = 1, int $perPage = 24, string $search = ''): array
    {
        $where  = '';
        $params = [];
        if ($search) {
            $where  = 'filename LIKE ? OR alt_text LIKE ?';
            $params = ["%{$search}%", "%{$search}%"];
        }
        return $this->paginate($page, $perPage, $where, $params, 'id', 'DESC');
    }

    public function upload(array $file, int $userId): int|false
    {
        $cfg      = require CONFIG_PATH . '/app.php';
        $allowed  = $cfg['upload']['allowed'];
        $maxSize  = $cfg['upload']['max_size'];

        if (!in_array($file['type'], $allowed)) return false;
        if ($file['size'] > $maxSize) return false;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_', true) . '.' . $ext;
        $folder   = date('Y/m');
        $dir      = $cfg['upload']['path'] . $folder;

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

        // Get image dimensions + resize + convert to WebP + thumbnail
        $width = $height = null;
        $finalMime = $file['type'];
        if (str_starts_with($file['type'], 'image/')) {
            [$width, $height] = @getimagesize($dest) ?: [null, null];
            // Resize original to max 1200px wide (saves bandwidth)
            if ($width && $width > 1200) {
                $this->createThumbnail($dest, $dest, 1200); // overwrite original
                [$width, $height] = @getimagesize($dest) ?: [$width, $height];
            }
            // Convert to WebP for storage efficiency (skip if already webp)
            $webpResult = $this->convertToWebp($dest, $dir, $filename);
            if ($webpResult !== $filename) {
                $filename  = $webpResult;
                $dest      = $dir . '/' . $filename;
                $finalMime = 'image/webp';
                [$width, $height] = @getimagesize($dest) ?: [$width, $height];
            }
            // Create 400px thumbnail for news cards
            $this->createThumbnail($dest, $dir . '/thumb_' . $filename, 400);
        }

        return $this->insert([
            'user_id'    => $userId,
            'filename'   => $file['name'],
            'filepath'   => $cfg['upload']['url_path'] . $folder . '/' . $filename,
            'thumb_path' => $cfg['upload']['url_path'] . $folder . '/thumb_' . $filename,
            'mime_type'  => $finalMime,
            'size'       => @filesize($dest) ?: $file['size'],
            'width'      => $width,
            'height'     => $height,
            'folder'     => $folder,
        ]);
    }

    /** Convert an image file to WebP, delete original, return new filename */
    private function convertToWebp(string $srcPath, string $dir, string $originalName): string
    {
        if (!function_exists('imagewebp')) return $originalName;

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext === 'webp') return $originalName;

        $info = @getimagesize($srcPath);
        if (!$info) return $originalName;

        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($srcPath),
            IMAGETYPE_GIF  => @imagecreatefromgif($srcPath),
            default        => null,
        };
        if (!$img) return $originalName;

        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $webpName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
        $webpPath = $dir . '/' . $webpName;

        if (imagewebp($img, $webpPath, 82)) {
            imagedestroy($img);
            @unlink($srcPath);
            return $webpName;
        }
        imagedestroy($img);
        return $originalName;
    }

    private function createThumbnail(string $src, string $dest, int $maxW): void
    {
        [$w, $h, $type] = @getimagesize($src) ?: [0, 0, 0];
        if (!$w || $w <= $maxW) { copy($src, $dest); return; }

        $ratio  = $maxW / $w;
        $newW   = $maxW;
        $newH   = (int)($h * $ratio);

        $source = match($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => null,
        };
        if (!$source) return;

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newW, $newH, $w, $h);

        match($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $dest, 82),
            IMAGETYPE_PNG  => imagepng($thumb, $dest, 8),
            IMAGETYPE_WEBP => imagewebp($thumb, $dest, 82),
            default        => null,
        };

        imagedestroy($source);
        imagedestroy($thumb);
    }

    public function deleteFile(int $id): bool
    {
        $media = $this->find($id);
        if (!$media) return false;

        $base = dirname(__DIR__, 2) . '/public';
        @unlink($base . $media['filepath']);
        @unlink($base . $media['thumb_path']);

        return $this->delete($id);
    }

    public function allFolders(): array
    {
        try {
            return $this->fetchAll(
                "SELECT COALESCE(folder,'general') AS folder, COUNT(*) AS count
                 FROM tn_media GROUP BY folder ORDER BY folder"
            );
        } catch (\Exception $e) { return []; }
    }

    public function moveToFolder(int $id, string $folder): void
    {
        try {
            $this->query("UPDATE tn_media SET folder = ? WHERE id = ?", [$folder, $id]);
        } catch (\Exception $e) {}
    }

}
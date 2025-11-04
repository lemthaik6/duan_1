<?php

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

    // ---------------------------
    // Simple file cache helpers
    // ---------------------------
    if (!function_exists('cache_set')) {
        function cache_set($key, $data, $ttl = 60)
        {
            $path = sys_get_temp_dir() . '/duan_cache_' . md5($key) . '.cache';
            $payload = [
                'expires' => time() + (int)$ttl,
                'data' => $data,
            ];
            @file_put_contents($path, serialize($payload));
            return true;
        }
    }

    if (!function_exists('cache_get')) {
        function cache_get($key)
        {
            $path = sys_get_temp_dir() . '/duan_cache_' . md5($key) . '.cache';
            if (!is_readable($path)) return false;
            $raw = @file_get_contents($path);
            if ($raw === false) return false;
            $payload = @unserialize($raw);
            if (!is_array($payload) || empty($payload['expires']) || !array_key_exists('data', $payload)) return false;
            if ($payload['expires'] < time()) {
                @unlink($path);
                return false;
            }
            return $payload['data'];
        }
    }

    if (!function_exists('cache_delete')) {
        function cache_delete($key)
        {
            $path = sys_get_temp_dir() . '/duan_cache_' . md5($key) . '.cache';
            if (is_file($path)) @unlink($path);
            return true;
        }
    }

    // ---------------------------
    // Safe log helpers (rotates large logs)
    // ---------------------------
    if (!function_exists('rotate_log_file')) {
        function rotate_log_file($path, $maxBytes = 5242880)
        {
            if (!is_file($path)) return;
            clearstatcache(true, $path);
            $size = filesize($path);
            if ($size === false) return;
            if ($size > $maxBytes) {
                $bak = $path . '.1';
                @rename($path, $bak);
            }
        }
    }

    if (!function_exists('safe_log_append')) {
        function safe_log_append($path, $message)
        {
            $dir = dirname($path);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            // rotate when too big (5MB default)
            rotate_log_file($path, 5 * 1024 * 1024);
            @file_put_contents($path, $message, FILE_APPEND | LOCK_EX);
        }
    }

    if (!function_exists('booking_log')) {
        function booking_log($message)
        {
            $path = PATH_ROOT . 'storage/booking.log';
            // If APP_DEBUG is false, keep messages concise (no full stacktrace), otherwise log full message.
            if (defined('APP_DEBUG') && APP_DEBUG === true) {
                safe_log_append($path, $message);
            } else {
                // Strip extra long traces but keep timestamp and main message
                // If message contains a stack trace, only keep first 6 lines
                $lines = explode("\n", $message);
                if (count($lines) > 12) $lines = array_slice($lines, 0, 12);
                safe_log_append($path, implode("\n", $lines) . "\n");
            }
        }
    }

if (!function_exists('upload_file')) {
    function upload_file($folder, $file)
    {
        // Basic validation
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Không có file được upload hoặc có lỗi upload.');
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $maxBytes) {
            throw new Exception('Kích thước file quá lớn. Giới hạn 5MB.');
        }

        // Validate MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowed[$mime])) {
            throw new Exception('Loại file không được hỗ trợ. Vui lòng upload JPG, PNG hoặc WEBP.');
        }

        // sanitize original name and make unique
        $ext = $allowed[$mime];
        $base = pathinfo($file['name'], PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9\- ]/', '', $base);
        $base = substr(str_replace(' ', '-', $base), 0, 50);
        $filename = $base . '-' . time() . '.' . $ext;

        $targetDir = PATH_ASSETS_UPLOADS . rtrim($folder, '/') . '/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $targetFull = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFull)) {
            // return relative path used by app (folder/filename)
            return rtrim($folder, '/') . '/' . $filename;
        }

        throw new Exception('Upload file không thành công!');
    }
}

if (!function_exists('resize_image')) {
    /**
     * Resize image using GD and save to the same path (overwrites target)
     * $sourcePath is full path to source file
     */
    function resize_image($sourcePath, $targetPath, $maxWidth = 1200, $maxHeight = 800)
    {
        if (!extension_loaded('gd')) return false;

        list($origW, $origH, $type) = getimagesize($sourcePath);
        if (!$origW || !$origH) return false;

        $ratio = min($maxWidth / $origW, $maxHeight / $origH, 1);
        $newW = (int)($origW * $ratio);
        $newH = (int)($origH * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($sourcePath);
                // preserve alpha
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                break;
            default:
                return false;
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // ensure target directory exists
        $dir = dirname($targetPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($dst, $targetPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($dst, $targetPath, 6);
                break;
        }

        imagedestroy($dst);
        imagedestroy($src);

        return $result;
    }
}

if (!function_exists('send_mail')) {
    /**
     * Simple mail helper using PHP mail(). For production use configure SMTP properly.
     */
    function send_mail($to, $subject, $body, $from = null)
    {
        $from = $from ?? ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        // If PHPMailer is available, use it (preferred)
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mailClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
                $mail = new $mailClass(true);

                // If SMTP settings are defined in environment (optional), configure
                if (defined('SMTP_HOST') && constant('SMTP_HOST')) {
                    $mail->isSMTP();
                    $mail->Host = constant('SMTP_HOST');
                    $mail->SMTPAuth = true;
                    if (defined('SMTP_USER')) $mail->Username = constant('SMTP_USER');
                    if (defined('SMTP_PASS')) $mail->Password = constant('SMTP_PASS');
                    $secure = defined('SMTP_SECURE') ? constant('SMTP_SECURE') : 'tls';
                    $mail->SMTPSecure = $secure;
                    $mail->Port = defined('SMTP_PORT') ? constant('SMTP_PORT') : 587;
                }

                $mail->setFrom($from);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;

                return $mail->send();
            } catch (Exception $e) {
                // fallback to mail()
            }
        }

        $headers = "From: " . $from . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        return mail($to, $subject, $body, $headers);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('validate_csrf')) {
    function validate_csrf($token)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['_csrf_token'])) return false;
        return hash_equals($_SESSION['_csrf_token'], (string)$token);
    }
}

if (!function_exists('create_thumbnail')) {
    /**
     * Create a thumbnail with exact box dimensions (fit inside the box) and save to targetPath
     */
    function create_thumbnail($sourcePath, $targetPath, $thumbWidth = 300, $thumbHeight = 200)
    {
        if (!extension_loaded('gd')) return false;

        list($origW, $origH, $type) = getimagesize($sourcePath);
        if (!$origW || !$origH) return false;

        $ratio = min($thumbWidth / $origW, $thumbHeight / $origH);
        $newW = (int)($origW * $ratio);
        $newH = (int)($origH * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($sourcePath);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                break;
            default:
                return false;
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $dir = dirname($targetPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($dst, $targetPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($dst, $targetPath, 6);
                break;
        }

        imagedestroy($dst);
        imagedestroy($src);
        return $result;
    }
}
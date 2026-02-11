<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dp_proxy extends CI_Controller {

    public function index($fileId = null, $fileName = null) {
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $requestId = substr(md5(uniqid()), 0, 8);

        if (!$fileId || !$fileName) {
            log_message('error', "[$requestId] Missing parameters");
            show_404();
        }

        // Sanitize
        $fileId = preg_replace('/[^0-9]/', '', $fileId);
        $fileName = basename($fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $fileName);

        if (empty($fileId) || empty($fileName)) {
            show_404();
        }

        $url = "https://digitalpreservation.is.ed.ac.uk/bitstream/handle/20.500.12734/{$fileId}/{$fileName}";

        log_message('info', "[$requestId] START: $url");

        // Get headers first with a HEAD request
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $headerResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400) {
            log_message('error', "[$requestId] HEAD request failed - HTTP $httpCode");
            http_response_code($httpCode);
            exit;
        }

        // Parse headers
        $contentType = null;
        $contentLength = null;
        $acceptRanges = false;

        foreach (explode("\r\n", $headerResponse) as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $contentType = trim(substr($header, 13));
            } elseif (stripos($header, 'Content-Length:') === 0) {
                $contentLength = trim(substr($header, 15));
            } elseif (stripos($header, 'Accept-Ranges:') === 0 && stripos($header, 'bytes') !== false) {
                $acceptRanges = true;
            }
        }

        if (!$contentType) {
            $contentType = $this->getMimeType($fileName);
        }

        log_message('info', "[$requestId] Content-Type: $contentType, Length: $contentLength, Accept-Ranges: " . ($acceptRanges ? 'yes' : 'no'));

        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Disable output buffering
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }

        // Handle range requests for video/audio seeking
        $range = null;
        if (isset($_SERVER['HTTP_RANGE']) && $acceptRanges) {
            $range = $_SERVER['HTTP_RANGE'];
            log_message('info', "[$requestId] Range request: $range");
        }

        // Stream the file directly
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 0,  // No timeout for large files
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_RETURNTRANSFER => false,  // CRITICAL: Don't buffer in memory
            CURLOPT_HEADER => false,
            CURLOPT_WRITEFUNCTION => function($curl, $data) {
                echo $data;
                return strlen($data);
            },
        ]);

        // Add range header if requested
        if ($range) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Range: ' . $range,
                'Accept-Encoding: identity',
            ]);

            // For range requests, we need to capture and forward the response headers
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
                $trimmed = trim($header);
                if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $trimmed, $matches)) {
                    $code = (int)$matches[1];
                    if ($code === 206) {
                        http_response_code(206);
                    }
                } elseif (stripos($trimmed, 'Content-Range:') === 0) {
                    header($trimmed, false);
                } elseif (stripos($trimmed, 'Content-Length:') === 0) {
                    header($trimmed, false);
                }
                return strlen($header);
            });
        } else {
            // Full file request
            if ($acceptRanges) {
                header('Accept-Ranges: bytes');
            }
            if ($contentLength) {
                header('Content-Length: ' . $contentLength);
            }
        }

        // Set headers
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        // Stream the content
        $result = curl_exec($ch);
        $finalHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false || ($finalHttpCode >= 400 && $finalHttpCode !== 416)) {
            log_message('error', "[$requestId] Stream failed - HTTP $finalHttpCode - " . ($error ?: 'no error'));
        } else {
            log_message('info', "[$requestId] Stream completed - HTTP $finalHttpCode");
        }

        exit;
    }

    private function getMimeType($fileName) {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf',
            'mp3'  => 'audio/mpeg',
            'wav'  => 'audio/wav',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'm4v'  => 'video/mp4',
            'mov'  => 'video/quicktime',
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}

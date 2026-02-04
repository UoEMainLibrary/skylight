<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('BASEPATH') or exit('No direct script access allowed');

class Dp_proxy extends CI_Controller {
    public function index($fileId = null, $fileName = null) {
        if (!$fileId || !$fileName) {
            show_404();
        }

        // Construct the source URL
        $url = "https://digitalpreservation.is.ed.ac.uk/bitstream/handle/20.500.12734/$fileId/$fileName";
        echo "Attempting to fetch: " . $originalUrl; // For debugging

        $headers = [
            'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0'),
        ];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => implode("\r\n", $headers),
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        // No output. Ever.
        while (ob_get_level()) {
            ob_end_clean();
        }

        $fp = @fopen($url, 'rb', false, $context);
        if (!$fp) {
            http_response_code(404);
            exit;
        }

        // Forward upstream headers
        $meta = stream_get_meta_data($fp);

        if (!empty($meta['wrapper_data'])) {
            foreach ($meta['wrapper_data'] as $h) {
                // Skip transfer-encoding to avoid double handling
                if (stripos($h, 'Transfer-Encoding:') === 0) {
                    continue;
                }
                header($h, false);
            }
        }

        header('Accept-Ranges: bytes');

        fpassthru($fp);
        fclose($fp);
        exit;
    }
}
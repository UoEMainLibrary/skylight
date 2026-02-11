<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('BASEPATH') or exit('No direct script access allowed');

class Dp_proxy extends CI_Controller {
    public function index($fileId = null, $fileName = null) {
        if (!$fileId || !$fileName) {
            show_404();
            return;
        }

        // Construct the source URL
        $url = "https://digitalpreservation.is.ed.ac.uk/bitstream/handle/20.500.12734/$fileId/$fileName";
        log_message('debug', "Attempting to fetch: $url");

        $headers = [
            'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0'),
        ];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }
        /*
                // Remove any cookies your PHP script might emit
        header_remove('Set-Cookie');

        // Remove unnecessary Vary headers
        header_remove('Vary');

        // Optional: set caching explicitly
        header('Cache-Control: public, max-age=3600');
        */

        header('Connection: close');


        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        ob_clean(); // Clear output buffer

        $fp = @fopen($url, 'rb', false, $context);
        if (!$fp) {
            http_response_code(404);
            echo 'Error: Unable to open the file.';
            exit;
        }

        // Forward upstream headers
        $meta = stream_get_meta_data($fp);

        if (!empty($meta['wrapper_data'])) {
            foreach ($meta['wrapper_data'] as $h) {
                // Handle content length and type explicitly
                if (stripos($h, 'Content-Type:') === 0 || stripos($h, 'Content-Length:') === 0) {
                    header($h, false);
                }
            }
        }

        header('Accept-Ranges: bytes');
        header('Connection: keep-alive');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        fpassthru($fp);
        fclose($fp);
        exit;
    }
}
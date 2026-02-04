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
        $originalUrl = "https://digitalpreservation.is.ed.ac.uk/bitstream/handle/20.500.12734/$fileId/$fileName";
        echo "Attempting to fetch: " . $originalUrl; // For debugging
 
        $ch = curl_init($originalUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            show_error('Error fetching file: ' . curl_error($ch), 500);
            curl_close($ch);
            return;
        }

        if (!$response) {
            show_404();
        }

        // Determine the content type from the cURL response
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // Clean output buffer and send headers
        ob_clean();
        header("Content-Type: $contentType");
        header("Content-Length: " . strlen($response));
        flush();
        echo $response;

        curl_close($ch);

    }
}
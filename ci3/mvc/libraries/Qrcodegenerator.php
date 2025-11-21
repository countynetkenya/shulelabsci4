<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;

class Qrcodegenerator
{
    public function generate_qrcode(string $text, string $filename, string $folder): void
    {
        $directory = FCPATH . 'uploads/' . $folder . '/';

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return;
        }

        $filePath = $directory . $filename . '.png';

        Builder::create()
            ->writer(new PngWriter())
            ->data($text)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(160)
            ->margin(4)
            ->build()
            ->saveToFile($filePath);
    }
}

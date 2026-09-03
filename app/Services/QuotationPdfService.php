<?php

namespace App\Services;

use App\Models\Quotation;

class QuotationPdfService
{
    public function render(Quotation $quotation): string
    {
        $vehicle = $quotation->vehicle_snapshot;
        $lines = [
            'TG WORLD INTERNATIONAL',
            'QUOTATION REQUEST PREVIEW',
            '',
            'Reference: '.$quotation->reference,
            'Date: '.$quotation->created_at->format('d M Y H:i'),
            'Status: '.strtoupper($quotation->status),
            '',
            'CUSTOMER',
            'Name: '.$quotation->full_name,
            'Email: '.$quotation->email,
            'Phone: '.$quotation->phone,
            '',
            'VEHICLE',
            'Vehicle: '.($vehicle['title'] ?? 'Vehicle #'.$quotation->car_id),
            'Year: '.($vehicle['year'] ?? 'N/A'),
            'Chassis: '.($vehicle['chassis'] ?? 'N/A'),
            'Colour: '.($vehicle['colour'] ?? 'N/A'),
            'Mileage: '.($vehicle['mileage'] ?? 'N/A'),
            'Listed price: '.($vehicle['listedPrice'] ?? 'N/A'),
            '',
            'CUSTOMER OFFER',
            $quotation->currency.' '.number_format($quotation->proposed_price),
            '',
            'This document is a request preview, not a final invoice or binding offer.',
        ];

        if ($quotation->delivery_address || $quotation->delivery_city) {
            $lines[] = '';
            $lines[] = 'DELIVERY';
            $lines[] = implode(', ', array_filter([
                $quotation->delivery_address,
                $quotation->delivery_city,
                $quotation->delivery_region,
                $quotation->delivery_postal_code,
            ]));
        }

        if ($quotation->customer_notes) {
            $lines[] = '';
            $lines[] = 'NOTES';
            foreach (str_split(mb_strimwidth($quotation->customer_notes, 0, 680, '...'), 85) as $line) {
                $lines[] = $line;
            }
        }

        return $this->pdf($lines);
    }

    private function pdf(array $lines): string
    {
        $commands = ['BT', '/F1 11 Tf', '50 790 Td'];
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $commands[] = '0 -18 Td';
            }
            $commands[] = '('.$this->escape($line).') Tj';
        }
        $commands[] = 'ET';
        $stream = implode("\n", $commands);

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($number + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function escape(mixed $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $value) ?: '';

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $ascii);
    }
}

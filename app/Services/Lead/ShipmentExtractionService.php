<?php

namespace App\Services\Lead;

class ShipmentExtractionService
{
    /**
     * Extract shipment information from email subject and body content.
     */
    public function extract(string $subject, string $bodyText, ?string $fromName = null, ?string $fromEmail = null): array
    {
        $content = $subject . "\n" . $bodyText;

        $data = [
            'incoterms' => $this->extractIncoterms($content),
            'shipment_type' => $this->extractShipmentType($content),
            'origin' => $this->extractOrigin($content),
            'destination' => $this->extractDestination($content),
            'pol' => $this->extractPol($content),
            'pod' => $this->extractPod($content),
            'pickup_address' => $this->extractPickupAddress($content),
            'delivery_address' => $this->extractDeliveryAddress($content),
            'commodity' => $this->extractCommodity($content),
            'weight' => $this->extractWeight($content),
            'dimensions' => $this->extractDimensions($content),
            'quantity' => $this->extractQuantity($content),
            'pallets' => $this->extractPallets($content),
            'container_type' => $this->extractContainerType($content),
            'shipment_date' => $this->extractShipmentDate($content),
            'customer_name' => $this->extractCustomerName($content, $fromName),
            'customer_phone' => $this->extractCustomerPhone($content),
            'company_name' => $this->extractCompanyName($content, $fromName, $fromEmail),
        ];

        $data['ai_summary'] = $this->generateSummary($data, $subject);

        return $data;
    }

    protected function extractIncoterms(string $content): ?string
    {
        if (preg_match('/\b(EXW|EX-WORKS|EX WORKS|EXWORKS|EX-W|EX W|DDP|FOB|CIF|DAP|CFR|FCA|DTP)\b/i', $content, $m)) {
            $val = strtoupper($m[1]);
            if (str_starts_with($val, 'EX')) {
                return 'EXW';
            }
            return $val;
        }
        return null;
    }

    protected function extractShipmentType(string $content): string
    {
        if (preg_match('/(reefer|deep frozen|-18\s*°?c|-180c|cold chain|2\s*to\s*8\s*°?c)/i', $content)) {
            return 'reefer';
        }
        if (preg_match('/(air\s*freight|air\s*import|airport|air|aod|aol)/i', $content)) {
            return 'air_freight';
        }
        if (preg_match('/(lcl|part\s*load|groupage)/i', $content)) {
            return 'sea_lcl';
        }
        if (preg_match('/(fcl|20\s*\'?\s*gp|40\s*\'?\s*hc|40\s*\'?\s*gp|20ft|40ft|teu|ocean-freight|ocean\s*freight|sea)/i', $content)) {
            return 'sea_fcl';
        }
        if (preg_match('/(trucking|road\s*freight|trailer|haulier)/i', $content)) {
            return 'road_freight';
        }
        return 'unknown';
    }

    protected function extractOrigin(string $content): ?string
    {
        if (preg_match('/(?:EXW|EX-WORKS|EX WORKS|from)\s+([A-Z0-9\s,\.\'-]{2,30}?)\s+(?:TO|UNTIL|UPTO|PORT|AIRPORT|-|\/\/)/i', $content, $m)) {
            $val = trim($m[1]);
            if (strlen($val) > 2 && !preg_match('/(rate|quote|freight|charges)/i', $val)) {
                return $val;
            }
        }
        if (preg_match('/POL\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/AOL\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(?:Pickup|Collection)\s*(?:location|address)?\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractDestination(string $content): ?string
    {
        if (preg_match('/(?:TO|POD|AOD|DESTINATION|UPTO)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(?:TO|UPTO)\s+([A-Z0-9\s,\.\'-]{2,30}?)\s*(?:PORT|AIRPORT|AIRPORT\b|PORT\b|\r|\n|$)/i', $content, $m)) {
            $val = trim($m[1]);
            if (!preg_match('/(the|our|your|my|below|above)/i', $val)) {
                return $val;
            }
        }
        return null;
    }

    protected function extractPol(string $content): ?string
    {
        if (preg_match('/POL\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/AOL\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractPod(string $content): ?string
    {
        if (preg_match('/POD\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/AOD\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/Port of Delivery\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractPickupAddress(string $content): ?string
    {
        if (preg_match('/(?:Place of pick up|Pickup address|Shipper Address|Collection Address)\s*:\s*([\s\S]{5,200}?)(?=\r?\n\r?\n|POD|AOD|Commodity|Delivery Term|Cargo|Equipment|Value|Phone|Best Regards|Thanks|$)/i', $content, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        return null;
    }

    protected function extractDeliveryAddress(string $content): ?string
    {
        if (preg_match('/(?:Final DDP Delivery Address|Delivery Address|Delivery Term)\s*:\s*([\s\S]{5,150}?)(?=\r?\n\r?\n|Please|customer|port|trucking|Best Regards|Thanks|$)/i', $content, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        return null;
    }

    protected function extractCommodity(string $content): ?string
    {
        if (preg_match('/(?:Commodity|CARGO|Material)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractWeight(string $content): ?string
    {
        if (preg_match('/(?:Gross weight|Material weight|Total Material weight|Total Gross Weight|Weight|Volume)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(\d+(?:\.\d+)?\s*(?:MT|kg|kgs|KGS|ton|tonnes|ton\.))/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractDimensions(string $content): ?string
    {
        if (preg_match('/(?:Dimension[s]?|size)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(\d+\s*x\s*\d+\s*x\s*\d+\s*(?:cm|mm|m|inch)?)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractQuantity(string $content): ?string
    {
        if (preg_match('/(?:No\.\s*of\s*Cases|Quantity|Pcs|Volume|Packaging)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(\d+\s*(?:Pcs|Cases|Cartons|Drums|Boxes|Packages|Units))/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractPallets(string $content): ?string
    {
        if (preg_match('/(?:Pallet Configuration|Pallets|Total Number of Pallet)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(\d+\s*(?:Standard\s*UK\s*pallets|pallets|pallet))/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractContainerType(string $content): ?string
    {
        if (preg_match('/(?:Equipment Required|EQUIP|Container|Volume)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(\d+\s*x\s*\d+[\'\"]?\s*(?:GP|HC|Reefer|FT)?(?:\s*\([^)]+\))?|\d+FT(?:\s*&\s*\d+H)?)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractShipmentDate(string $content): ?string
    {
        if (preg_match('/(?:Cargo readiness date|Ready for pickup|Readiness)\s*:\s*([^\r\n]+)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractCustomerName(string $content, ?string $fromName = null): string
    {
        if (preg_match('/(?:Best Regards|Thanks & Best Regards|Regards|Thanks),\s*([A-Za-z\s\.]{2,30})/i', $content, $m)) {
            $name = trim($m[1]);
            if (strlen($name) > 2 && !preg_match('/(Team|Partner|Dears|Sir|All)/i', $name)) {
                return $name;
            }
        }
        if (!empty($fromName)) {
            return $fromName;
        }
        return 'Unknown Customer';
    }

    protected function extractCustomerPhone(string $content): ?string
    {
        if (preg_match('/(?:Phone|Tel|Mobile|Call)\s*[:\.\s]*(\+?\d[\d\s\-\(\)]{7,18}\d)/i', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractCompanyName(string $content, ?string $fromName = null, ?string $fromEmail = null): ?string
    {
        if (preg_match('/([A-Z0-9\s\.\,\'-]{2,50}\s*(?:Ltd|Limited|A\/S|S\.A\.|NV|GmbH|Inc|LLC|Corp|Co\.))/i', $content, $m)) {
            return trim($m[1]);
        }
        if (!empty($fromEmail) && str_contains($fromEmail, '@')) {
            $domain = explode('@', $fromEmail)[1] ?? '';
            $parts = explode('.', $domain);
            if (count($parts) > 1 && !in_array($parts[0], ['gmail', 'yahoo', 'hotmail', 'outlook', 'icloud', 'protonmail'])) {
                return ucfirst($parts[0]);
            }
        }
        return null;
    }

    protected function generateSummary(array $data, string $subject): string
    {
        $type = match ($data['shipment_type'] ?? '') {
            'sea_fcl' => 'Sea FCL',
            'sea_lcl' => 'Sea LCL',
            'air_freight' => 'Air Freight',
            'road_freight' => 'Road Freight',
            'reefer' => 'Reefer Container',
            default => 'General Freight',
        };

        $commodity = !empty($data['commodity']) ? $this->cleanShortText($data['commodity'], 50) : 'General Cargo';
        $origin = !empty($data['origin']) ? $this->cleanShortText($data['origin'], 50) : (!empty($data['pol']) ? $this->cleanShortText($data['pol'], 35) : null);
        $destination = !empty($data['destination']) ? $this->cleanShortText($data['destination'], 50) : (!empty($data['pod']) ? $this->cleanShortText($data['pod'], 35) : null);
        $incoterms = !empty($data['incoterms']) ? strtoupper($data['incoterms']) : null;

        $lines = [];
        $lines[] = "• Inquiry Mode & Cargo: {$type} inquiry for {$commodity}";

        if ($origin || $destination) {
            $routeStr = "• Route: " . ($origin ?: 'TBD') . " ➔ " . ($destination ?: 'TBD');
            if ($incoterms) {
                $routeStr .= " ({$incoterms} Terms)";
            }
            $lines[] = $routeStr;
        } elseif ($incoterms) {
            $lines[] = "• Terms: {$incoterms}";
        }

        $specs = [];
        if (!empty($data['container_type'])) $specs[] = "Equipment: " . $this->cleanShortText($data['container_type'], 40);
        if (!empty($data['weight'])) $specs[] = "Weight: " . $this->cleanShortText($data['weight'], 30);
        if (!empty($data['pallets'])) $specs[] = "Pallets: " . $this->cleanShortText($data['pallets'], 30);

        if (!empty($specs)) {
            $lines[] = "• Shipment Specs: " . implode(" | ", $specs);
        }

        return implode("\n", $lines);
    }

    protected function cleanShortText(?string $str, int $limit = 60): string
    {
        if (empty($str)) return '';
        $text = html_entity_decode(strip_tags($str), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/(Best Regards|Thanks|Subject:|From:|Cc:|To:|Phone:|Email:).*/is', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit) . '...';
        }
        return $text;
    }
}

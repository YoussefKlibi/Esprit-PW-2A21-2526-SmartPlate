<?php
require_once __DIR__ . "/../../config.php";

class GeocodingService {

    public function getCoordinates($adresse, $ville, $codePostal, $pays) {

        $query = [
            'q' => $adresse . ', ' . $ville . ', ' . $codePostal . ', ' . $pays,
            'format' => 'json',
            'limit' => 1
        ];
        $url = "https://nominatim.openstreetmap.org/search?" . http_build_query($query);

        $options = [
            "http" => [
                "header" => "User-Agent: SmartPlate/1.0\r\n"
            ]
        ];

        sleep(1); // anti-rate-limit

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data[0])) {
            return [
                "lat" => $data[0]["lat"],
                "lon" => $data[0]["lon"]
            ];
        }

        return null;
    }
}
?>

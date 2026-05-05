<?php
require_once __DIR__ . "/../Config/config.php";
class GeocodingService {

    public function getCoordinates($adresse, $ville, $codePostal, $pays) {

        $fullAddress = urlencode($adresse . ", " . $ville . ", " . $codePostal . ", " . $pays);

        $url = "https://nominatim.openstreetmap.org/search?q=$fullAddress&format=json";

        $options = [
            "http" => [
                "header" => "User-Agent: MyApp/1.0\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        $data = json_decode($response, true);

        if (!empty($data)) {
            return [
                "lat" => $data[0]["lat"],
                "lon" => $data[0]["lon"]
            ];
        }

        return null;
    }
}
?>

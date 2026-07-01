<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Database};

class DistrictController extends Controller
{
    // GET /api/district/detect?lat=&lng=
    public function detect(): void
    {
        header('Content-Type: application/json');
        $lat = (float)($_GET['lat'] ?? 0);
        $lng = (float)($_GET['lng'] ?? 0);

        if (!$lat || !$lng) {
            echo json_encode(['error' => 'Missing coordinates']); exit;
        }

        // Tamil Nadu bounding box
        if ($lat < 8.0 || $lat > 13.6 || $lng < 76.2 || $lng > 80.4) {
            echo json_encode(['error' => 'Outside Tamil Nadu']); exit;
        }

        $apiKey = $_ENV['OPENCAGE_API_KEY'] ?? getenv('OPENCAGE_API_KEY') ?? '';
        $districtName = null;
        $cityName     = null;

        // ── OpenCage reverse geocode (server-side, key never exposed) ──
        if ($apiKey) {
            try {
                $url = 'https://api.opencagedata.com/geocode/v1/json?'
                     . http_build_query([
                           'q'              => $lat . '+' . $lng,
                           'key'            => $apiKey,
                           'language'       => 'en',
                           'no_annotations' => 1,
                           'countrycode'    => 'in',
                           'limit'          => 1,
                       ]);

                $ctx = stream_context_create(['http' => [
                    'timeout' => 5,
                    'header'  => "User-Agent: ThinaThulir/1.0\r\n",
                ]]);
                $raw = @file_get_contents($url, false, $ctx);

                if ($raw) {
                    $data = json_decode($raw, true);
                    $comp = $data['results'][0]['components'] ?? [];

                    // OpenCage returns county = district in India
                    $districtName = $comp['county']
                                 ?? $comp['state_district']
                                 ?? null;

                    // City: most specific populated place name
                    $cityName = $comp['city']
                             ?? $comp['town']
                             ?? $comp['village']
                             ?? $comp['suburb']
                             ?? $comp['county']
                             ?? null;
                }
            } catch (\Exception $e) {}
        }

        // ── Match district name to our tn_districts table ──
        $db = Database::getInstance();
        $district = null;

        if ($districtName) {
            // Fuzzy match: find closest name in our districts
            $stmt = $db->prepare(
                "SELECT id, name, slug FROM tn_districts
                 WHERE name LIKE ? OR name LIKE ?
                 LIMIT 1"
            );
            $clean = preg_replace('/\s+district/i', '', $districtName);
            $stmt->execute(['%' . $clean . '%', '%' . $districtName . '%']);
            $district = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // ── Fallback: nearest centroid if OpenCage match failed ──
        if (!$district) {
            $stmt = $db->prepare(
                "SELECT id, name, slug,
                        (POW(lat_center - ?, 2) + POW(lng_center - ?, 2)) AS dist_sq
                 FROM tn_districts
                 WHERE lat_center IS NOT NULL
                 ORDER BY dist_sq ASC LIMIT 1"
            );
            $stmt->execute([$lat, $lng]);
            $district = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$district) { echo json_encode(['error' => 'No district found']); exit; }

        // Store in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['tn_district_id']   = $district['id'];
            $_SESSION['tn_district_name'] = $district['name'];
            $_SESSION['tn_city_name']     = $cityName ?? $district['name'];
        }

        echo json_encode([
            'district_id'   => $district['id'],
            'district_name' => $district['name'],
            'district_slug' => $district['slug'],
            'city_name'     => $cityName ?? $district['name'],
        ]);
        exit;
    }

    // GET /api/districts — all districts list for manual selector
    public function all(): void
    {
        header('Content-Type: application/json');
        try {
            $db   = Database::getInstance();
            $stmt = $db->query("SELECT id, name, slug FROM tn_districts ORDER BY name ASC");
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    // POST /api/district/set — manual district selection
    public function set(): void
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['district_id'] ?? 0);
        if (!$id) { echo json_encode(['error'=>'Invalid']); exit; }

        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT id, name FROM tn_districts WHERE id=?");
            $stmt->execute([$id]);
            $d = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$d) { echo json_encode(['error'=>'Not found']); exit; }

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['tn_district_id']   = $d['id'];
                $_SESSION['tn_district_name'] = $d['name'];
            }

            echo json_encode(['ok' => true, 'district_id' => $d['id'], 'district_name' => $d['name']]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'DB error']);
        }
        exit;
    }
}

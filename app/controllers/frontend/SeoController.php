<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class SeoController extends Controller
{
    /** Base site URL — always read from BASE_URL (.env), never a DB
     *  setting that could go stale or be unconfigured (was previously
     *  falling back to a fake "https://example.com" placeholder).
     *  This also makes the eventual domain migration a single .env
     *  change with zero code edits needed here. */
    private function siteUrl(): string
    {
        return rtrim(defined('BASE_URL') ? BASE_URL . '/public' : 'http://localhost', '/');
    }

    private function esc(string $url): string
    {
        return htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** GET /sitemap.xml — all published articles + category pages */
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $baseUrl    = $this->siteUrl();
        $articles   = (new FrontendArticleModel())->latest(1000);
        $categories = (new CategoryModel())->topLevel();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo "<url><loc>{$this->esc($baseUrl)}/</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>";
        foreach ($categories as $c) {
            echo "<url><loc>{$this->esc($baseUrl . '/tamil-news/' . $c['slug'])}</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>";
        }
        foreach ($articles as $a) {
            $date = date('Y-m-d', strtotime($a['published_at']));
            echo "<url><loc>{$this->esc($baseUrl . '/article/' . $a['slug'])}</loc><lastmod>{$date}</lastmod><changefreq>daily</changefreq><priority>0.7</priority></url>";
        }
        echo '</urlset>';
        exit;
    }

    /** GET /sitemap-news.xml — Google News requires ONLY articles from
     *  the last 48 hours; older entries should not be in this feed. */
    public function sitemapNews(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $baseUrl  = $this->siteUrl();
        $settings = new SettingModel();
        $siteName = $settings->getValue('site_name', 'தினத்துளிர்');
        $articles = (new FrontendArticleModel())->latest(200);

        $cutoff = strtotime('-48 hours');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        foreach ($articles as $a) {
            $pubTs = strtotime($a['published_at']);
            if ($pubTs < $cutoff) continue; // Google News: 48-hour window only
            $pubDate = date('Y-m-d\TH:i:sP', $pubTs);
            echo "<url>
              <loc>{$this->esc($baseUrl . '/article/' . $a['slug'])}</loc>
              <news:news>
                <news:publication><news:name>" . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . "</news:name><news:language>ta</news:language></news:publication>
                <news:publication_date>{$pubDate}</news:publication_date>
                <news:title>" . htmlspecialchars($a['title'], ENT_XML1, 'UTF-8') . "</news:title>
              </news:news>
            </url>";
        }
        echo '</urlset>';
        exit;
    }

    /** GET /sitemap-images.xml — image sitemap for featured article images */
    public function sitemapImages(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $baseUrl  = $this->siteUrl();
        $articles = (new FrontendArticleModel())->latest(1000);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        foreach ($articles as $a) {
            if (empty($a['image_url'])) continue;
            $imgUrl = str_starts_with($a['image_url'], 'http') ? $a['image_url'] : $baseUrl . $a['image_url'];
            echo "<url>
              <loc>{$this->esc($baseUrl . '/article/' . $a['slug'])}</loc>
              <image:image>
                <image:loc>{$this->esc($imgUrl)}</image:loc>
                <image:title>" . htmlspecialchars($a['title'], ENT_XML1, 'UTF-8') . "</image:title>
              </image:image>
            </url>";
        }
        echo '</urlset>';
        exit;
    }

    /** GET /sitemap-index.xml — references all sub-sitemaps */
    public function sitemapIndex(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $baseUrl = $this->siteUrl();
        $today   = date('Y-m-d');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach (['sitemap.xml', 'sitemap-news.xml', 'sitemap-images.xml'] as $file) {
            echo "<sitemap><loc>{$this->esc($baseUrl . '/' . $file)}</loc><lastmod>{$today}</lastmod></sitemap>";
        }
        echo '</sitemapindex>';
        exit;
    }

    /** GET /robots.txt */
    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $baseUrl = $this->siteUrl();
        echo "User-agent: *\n"
           . "Allow: /\n"
           . "Disallow: /admin/\n"
           . "Disallow: /portal/\n"
           . "Disallow: /contribute/\n"
           . "Disallow: /auth/\n"
           . "Disallow: /api/\n"
           . "Disallow: /search?\n\n"
           . "Sitemap: {$baseUrl}/sitemap-index.xml\n"
           . "Sitemap: {$baseUrl}/sitemap.xml\n"
           . "Sitemap: {$baseUrl}/sitemap-news.xml\n";
        exit;
    }
}
<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{FrontendArticleModel, CategoryModel, SettingModel};

class SeoController extends Controller
{
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $settings  = new SettingModel();
        $baseUrl   = rtrim($settings->getValue('site_url', 'https://example.com'), '/');
        $articles  = (new FrontendArticleModel())->latest(1000);
        $categories = (new CategoryModel())->topLevel();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo "<url><loc>{$baseUrl}/</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>";
        foreach ($categories as $c) {
            echo "<url><loc>{$baseUrl}/tamil-news/{$c['slug']}</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>";
        }
        foreach ($articles as $a) {
            $date = date('Y-m-d', strtotime($a['published_at']));
            echo "<url><loc>{$baseUrl}/article/{$a['slug']}</loc><lastmod>{$date}</lastmod><changefreq>daily</changefreq><priority>0.7</priority></url>";
        }
        echo '</urlset>';
        exit;
    }

    public function sitemapNews(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $settings = new SettingModel();
        $baseUrl  = rtrim($settings->getValue('site_url', 'https://example.com'), '/');
        $siteName = $settings->getValue('site_name', 'Tamil News Portal');
        $articles = (new FrontendArticleModel())->latest(1000);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        foreach ($articles as $a) {
            $pubDate = date('Y-m-d\TH:i:sP', strtotime($a['published_at']));
            echo "<url>
              <loc>{$baseUrl}/article/{$a['slug']}</loc>
              <news:news>
                <news:publication><news:name>{$siteName}</news:name><news:language>ta</news:language></news:publication>
                <news:publication_date>{$pubDate}</news:publication_date>
                <news:title>" . htmlspecialchars($a['title']) . "</news:title>
              </news:news>
            </url>";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(): void
    {
        header('Content-Type: text/plain');
        $settings = new SettingModel();
        $baseUrl  = rtrim($settings->getValue('site_url', 'https://example.com'), '/');
        echo "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /contribute/\nDisallow: /auth/\n\nSitemap: {$baseUrl}/sitemap.xml\nSitemap: {$baseUrl}/sitemap-news.xml\n";
        exit;
    }

    public function sitemapIndex(): void
    {
        $this->sitemap();
    }

}
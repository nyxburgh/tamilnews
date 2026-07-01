<?php
namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\{CategoryModel, SettingModel};

class TrustPageController extends Controller
{
    private function render(string $view, string $metaTitle): void
    {
        $settings      = new SettingModel();
        $siteName      = $settings->getValue('site_name', 'தினத்துளிர்');
        $navCategories = (new CategoryModel())->allWithParent();
        $this->view('frontend.trust.' . $view, [
            'siteName'      => $siteName,
            'navCategories' => $navCategories,
            'metaTitle'     => $metaTitle . ' | ' . $siteName,
            'robotsContent' => 'index, follow',
            'categoryId'    => 0,
            'breaking'      => [],
        ], 'frontend');
    }

    public function about():            void { $this->render('about',               'எங்களைப் பற்றி'); }
    public function contact():          void { $this->render('contact',             'தொடர்பு கொள்ள'); }
    public function privacy():          void { $this->render('privacy',             'தனியுரிமைக் கொள்கை'); }
    public function terms():            void { $this->render('terms',               'பயன்பாட்டு விதிமுறைகள்'); }
    public function editorial():        void { $this->render('editorial',           'ஆசிரியக் கொள்கை'); }
    public function corrections():      void { $this->render('corrections',         'திருத்தக் கொள்கை'); }
    public function factChecking():     void { $this->render('fact-checking',       'உண்மைச் சரிபார்ப்பு கொள்கை'); }
    public function ethicsPolicy():     void { $this->render('ethics-policy',       'ஊடக நெறிமுறைக் கொள்கை'); }
    public function ownership():        void { $this->render('ownership',           'உரிமையாளர் விவரங்கள்'); }
    public function advertisingPolicy():void { $this->render('advertising-policy',  'விளம்பரக் கொள்கை'); }
    public function copyrightPolicy():  void { $this->render('copyright-policy',    'பதிப்புரிமைக் கொள்கை'); }
    public function grievance():        void { $this->render('grievance',           'குறைதீர் அலுவலர்'); }
    public function aiContentPolicy():  void { $this->render('ai-content-policy',   'AI உள்ளடக்கக் கொள்கை'); }
    public function info():             void { $this->render('info',              'தகவல் மையம்'); }
}

<?php
/**
 * UI Language Strings
 * app/lang/ui_strings.php
 * Usage: $L = uiStrings(getLang());
 */

function getLang(): string {
    $allowed = ['ta', 'en', 'hi'];
    // 1. Cookie preference
    $lang = $_COOKIE['site_lang'] ?? null;
    // 2. Session
    if (!$lang) $lang = \App\Core\Session::get('site_lang');
    // 3. Browser Accept-Language
    if (!$lang) {
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (str_contains($accept, 'hi')) $lang = 'hi';
        elseif (str_contains($accept, 'ta')) $lang = 'ta';
        else $lang = 'ta'; // default Tamil
    }
    return in_array($lang, $allowed) ? $lang : 'ta';
}

function uiStrings(string $lang = 'ta'): array {
    $strings = [
        'ta' => [
            // Nav
            'home'           => 'முகப்பு',
            'search'         => 'தேடல்',
            'search_ph'      => 'செய்திகளை தேடுங்கள்...',
            'search_ph_nav'  => 'தேடு...',
            'login'          => 'உள்நுழை',
            'logout'         => 'வெளியேறு',
            'menu'           => 'மெனு',
            'breaking'       => 'உடனடி',
            // Mob topbar
            'online_edition' => 'Online Edition',
            // Drawer
            'epaper'         => 'இ-பேப்பர்',
            'write'          => 'கட்டுரை எழுது',
            'contribute'     => 'பங்களிப்பு',
            // Modal
            'welcome'        => 'Welcome Back!',
            'login_subtitle' => 'செய்திகளை மதிப்பிட உள்நுழையுங்கள்',
            'rate_news'      => 'செய்திகளை மதிப்பிடுங்கள்',
            'comment'        => 'கருத்துகள் சொல்லுங்கள்',
            'notifications'  => 'உடனடி அறிவிப்புகள்',
            'sign_in_with'   => 'Sign in with',
            'google_login'   => 'Google மூலம் உள்நுழைக',
            'terms_note'     => 'உள்நுழைவதன் மூலம்',
            'terms_link'     => 'விதிமுறைகளை',
            'terms_accept'   => 'ஏற்கிறீர்கள்.',
            // Footer
            'privacy'        => 'Privacy Policy',
            'tagline'        => 'அரசியல் பழகு · அறம் செய்',
            // Article page
            'read_more'      => 'மேலும் படிக்க',
            'share'          => 'பகிர்',
            'related'        => 'தொடர்பான செய்திகள்',
            'views'          => 'பார்வைகள்',
            'published'      => 'வெளியிடப்பட்டது',
            'by'             => 'by',
            // Reg bar
            'reg_no'         => 'பதிவு எண்',
            'lang_label'     => 'தமிழ்',
        ],
        'en' => [
            'home'           => 'Home',
            'search'         => 'Search',
            'search_ph'      => 'Search news...',
            'search_ph_nav'  => 'Search...',
            'login'          => 'Login',
            'logout'         => 'Logout',
            'menu'           => 'Menu',
            'breaking'       => 'Breaking',
            'online_edition' => 'Online Edition',
            'epaper'         => 'E-Paper',
            'write'          => 'Write Article',
            'contribute'     => 'Contribute',
            'welcome'        => 'Welcome Back!',
            'login_subtitle' => 'Login to rate and comment on news',
            'rate_news'      => 'Rate news articles',
            'comment'        => 'Share your comments',
            'notifications'  => 'Get instant notifications',
            'sign_in_with'   => 'Sign in with',
            'google_login'   => 'Continue with Google',
            'terms_note'     => 'By signing in you agree to our',
            'terms_link'     => 'Terms of Service',
            'terms_accept'   => '',
            'privacy'        => 'Privacy Policy',
            'tagline'        => 'Political Awareness · Act Righteously',
            'read_more'      => 'Read More',
            'share'          => 'Share',
            'related'        => 'Related News',
            'views'          => 'Views',
            'published'      => 'Published',
            'by'             => 'by',
            'reg_no'         => 'Reg. No',
            'lang_label'     => 'English',
        ],
        'hi' => [
            'home'           => 'मुख्य पृष्ठ',
            'search'         => 'खोज',
            'search_ph'      => 'समाचार खोजें...',
            'search_ph_nav'  => 'खोजें...',
            'login'          => 'लॉग इन',
            'logout'         => 'लॉग आउट',
            'menu'           => 'मेनू',
            'breaking'       => 'ब्रेकिंग',
            'online_edition' => 'ऑनलाइन संस्करण',
            'epaper'         => 'ई-पेपर',
            'write'          => 'लेख लिखें',
            'contribute'     => 'योगदान करें',
            'welcome'        => 'वापस आपका स्वागत है!',
            'login_subtitle' => 'समाचार रेट करने के लिए लॉग इन करें',
            'rate_news'      => 'समाचार रेट करें',
            'comment'        => 'अपनी राय साझा करें',
            'notifications'  => 'तत्काल सूचनाएं पाएं',
            'sign_in_with'   => 'के साथ साइन इन करें',
            'google_login'   => 'Google से जारी रखें',
            'terms_note'     => 'साइन इन करके आप हमारी',
            'terms_link'     => 'सेवा की शर्तों',
            'terms_accept'   => 'से सहमत हैं।',
            'privacy'        => 'गोपनीयता नीति',
            'tagline'        => 'राजनीतिक जागरूकता · धर्म का पालन',
            'read_more'      => 'और पढ़ें',
            'share'          => 'शेयर करें',
            'related'        => 'संबंधित समाचार',
            'views'          => 'दृश्य',
            'published'      => 'प्रकाशित',
            'by'             => 'द्वारा',
            'reg_no'         => 'रजि. सं.',
            'lang_label'     => 'हिन्दी',
        ],
    ];

    return $strings[$lang] ?? $strings['ta'];
}

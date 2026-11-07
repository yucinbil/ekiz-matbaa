<?php
/**
 * Ekiz Matbaa - Porto Child Theme functions.php
 * Web: https://www.ekizmatbaa.com
 */

if (!defined('ABSPATH')) {
    exit; // Doğrudan erişimi engelle
}

// --------------------------------------------------------------------------
// 1. ÖZEL CSS DOSYASINI YÜKLEME
// --------------------------------------------------------------------------
add_action('wp_enqueue_scripts', 'ekiz_enqueue_custom_styles', 99);
function ekiz_enqueue_custom_styles() {
    // Child theme dizinindeki homepage-styles.css dosyasını dahil et
    if (file_exists(get_stylesheet_directory() . '/homepage-styles.css')) {
        wp_enqueue_style(
            'ekiz-homepage-styles',
            get_stylesheet_directory_uri() . '/homepage-styles.css',
            array('porto-theme'),
            filemtime(get_stylesheet_directory() . '/homepage-styles.css')
        );
    }
}

// --------------------------------------------------------------------------
// 2. KIRIK VE EKSİK INSTAGRAM KISA KODUNU ETKİSİZ HALE GETİRME
// --------------------------------------------------------------------------
// [jr_instagram] eklentisi kaldırıldığı için ekranda ham yazı kalmasını önler
add_shortcode('jr_instagram', 'ekiz_suppress_broken_instagram_shortcode');
function ekiz_suppress_broken_instagram_shortcode($atts) {
    return ''; // Ekrana ham metin basılmasını engeller, boş döner
}

// --------------------------------------------------------------------------
// 3. STATİK SAYFALARDA YAZAR (ADMIN) VE TARİH BİLGİSİNİ KALDIRMA
// --------------------------------------------------------------------------
add_filter('porto_page_title', 'ekiz_filter_page_title_meta');
function ekiz_filter_page_title_meta($title) {
    return $title;
}

// --------------------------------------------------------------------------
// 4. HEADER ARAMA (SEARCH) ÇEVİRİSİ VE TAM İŞLEVSELLEŞTİRME
// --------------------------------------------------------------------------
add_filter('gettext', 'ekiz_translate_search_strings', 20, 3);
function ekiz_translate_search_strings($translated_text, $text, $domain) {
    if ($text === 'Search' || $text === 'Search...') {
        return 'Ürün veya Hizmet Ara...';
    }
    return $translated_text;
}

// Arama formu içeriğinin kesinlikle basılmasını garantiye al
add_filter('porto_search_form', 'ekiz_ensure_search_form_html', 99);
function ekiz_ensure_search_form_html($html) {
    if (strpos($html, '<form') === false) {
        $home = esc_url(home_url('/'));
        $search_form = '<form action="' . $home . '" method="get" class="searchform search-layout-simple ekiz-popup-searchform">
            <div class="searchform-fields">
                <span class="text">
                    <input name="s" type="text" value="" placeholder="Ürün, kutu, katalog veya hizmet ara..." autocomplete="off" />
                </span>
                <span class="button-wrap">
                    <button class="btn btn-special" aria-label="Ara" type="submit">
                        <i class="porto-icon-magnifier"></i>
                    </button>
                </span>
            </div>
        </form>';
        $html = str_replace('</a></div>', '</a>' . $search_form . '</div>', $html);
    }
    return $html;
}

// Arama ikonuna tıklandığında popup formunun açılıp kapanması
add_action('wp_footer', 'ekiz_search_toggle_script', 99);
function ekiz_search_toggle_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var popups = document.querySelectorAll('.searchform-popup');
        popups.forEach(function(popup) {
            var toggle = popup.querySelector('.search-toggle');
            var form = popup.querySelector('.searchform');
            if (toggle && form) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isVisible = form.classList.contains('ekiz-search-open');
                    document.querySelectorAll('.searchform.ekiz-search-open').forEach(function(f) {
                        f.classList.remove('ekiz-search-open');
                    });
                    document.querySelectorAll('.search-toggle.opened').forEach(function(t) {
                        t.classList.remove('opened');
                    });
                    if (!isVisible) {
                        form.classList.add('ekiz-search-open');
                        toggle.classList.add('opened');
                        var input = form.querySelector('input[name="s"]');
                        if (input) setTimeout(function(){ input.focus(); }, 100);
                    }
                });
            }
        });
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.searchform-popup')) {
                document.querySelectorAll('.searchform.ekiz-search-open').forEach(function(f) {
                    f.classList.remove('ekiz-search-open');
                });
                document.querySelectorAll('.search-toggle.opened').forEach(function(t) {
                    t.classList.remove('opened');
                });
            }
        });
    });
    </script>
    <?php
}

// --------------------------------------------------------------------------
// 5. XML-RPC VE GEREKSİZ META BİLGİLERİNİ KAPATMA (GÜVENLİK)
// --------------------------------------------------------------------------
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');

// --------------------------------------------------------------------------
// 6. GOOGLE SEO VE YAPAY ZEKA (AI) KNOWLEDGE GRAPH & SCHEMA.ORG JSON-LD
// --------------------------------------------------------------------------
add_action('wp_head', 'ekiz_inject_rich_seo_and_ai_schema', 2);
function ekiz_inject_rich_seo_and_ai_schema() {
    $schema_graph = array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            // 1. Yerel İşletme & Matbaa Fabrikası (LocalBusiness / PrintShop)
            array(
                '@type'         => array('PrintShop', 'LocalBusiness'),
                '@id'           => 'https://www.ekizmatbaa.com/#printshop',
                'name'          => 'Ekiz Matbaa',
                'alternateName' => array('Ekiz Matbaacılık', 'Ekiz Ambalaj ve Ofset Matbaa', 'Ekiz Ofset'),
                'legalName'     => 'Ekiz Matbaacılık',
                'url'           => 'https://www.ekizmatbaa.com/',
                'sameAs'        => array(
                    'https://github.com/yucinbil/ekiz-matbaa',
                    'https://gist.github.com/yucinbil/57736c8e1b4dbb2d463e90779663477e',
                    'https://maps.google.com/?q=41.038402,28.922808'
                ),
                'logo'          => 'https://www.ekizmatbaa.com/wp-content/uploads/2022/11/ekiz-4.png',
                'image'         => 'https://www.ekizmatbaa.com/wp-content/uploads/2022/11/ekiz-4.png',
                'description'   => "İstanbul Eyüpsultan Topçular'da 35+ yıllık üretim tecrübesiyle lüks karton kutu ambalaj, katalog, broşür, karton çanta, etiket ve kurumsal ofset matbaa çözümleri sunan entegre matbaa fabrikası.",
                'telephone'     => array('+902126124053', '+905363939306'),
                'email'         => 'info@ekizmatbaa.com',
                'foundingDate'  => '1987',
                'priceRange'    => '₺₺',
                'currenciesAccepted' => 'TRY, USD, EUR',
                'paymentAccepted'    => 'Nakit, Kredi Kartı, Havale / EFT',
                'address'       => array(
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => 'Topçular Mah. Demirkapı Cad. Paşmakçı Sanayi Sitesi No:20/82',
                    'addressLocality' => 'Eyüpsultan',
                    'addressRegion'   => 'İstanbul',
                    'postalCode'      => '34055',
                    'addressCountry'  => 'TR'
                ),
                'geo'           => array(
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => 41.038402,
                    'longitude' => 28.922808
                ),
                'hasMap'        => 'https://maps.google.com/?q=41.038402,28.922808',
                'openingHoursSpecification' => array(
                    array(
                        '@type'     => 'OpeningHoursSpecification',
                        'dayOfWeek' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                        'opens'     => '08:30',
                        'closes'    => '19:00'
                    ),
                    array(
                        '@type'     => 'OpeningHoursSpecification',
                        'dayOfWeek' => array('Saturday'),
                        'opens'     => '08:30',
                        'closes'    => '15:00'
                    )
                ),
                'areaServed'    => array(
                    array('@type' => 'AdministrativeArea', 'name' => 'İstanbul'),
                    array('@type' => 'Country', 'name' => 'Türkiye'),
                    array('@type' => 'AdministrativeArea', 'name' => 'Marmara Bölgesi')
                ),
                'knowsAbout'    => array(
                    'Ofset Baskı',
                    'Lüks Kutu Ambalaj',
                    'Mukavva Sıvamalı Kutu İmalatı',
                    'Katalog Baskı',
                    'Broşür Baskı',
                    'Karton Çanta İmalatı',
                    'Rulo ve Tabaka Etiket Baskı',
                    'Heidelberg Ofset Baskı Teknolojisi',
                    'Varak Yaldız ve Gofre Kabartma',
                    'Lokal UV Lak ve Selefon Kaplama',
                    'Topçular Matbaacılar Sitesi',
                    'Eyüpsultan Matbaa Fabrikası'
                ),
                'hasOfferCatalog' => array(
                    '@type' => 'OfferCatalog',
                    'name'  => 'Matbaa ve Ambalaj Üretim Hizmetleri',
                    'itemListElement' => array(
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Lüks Karton Kutu ve Sert Ambalaj İmalatı')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Kurumsal Katalog, Dergi ve Broşür Baskısı')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Özel Tasarım Karton Çanta ve Poşet İmalatı')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Rulo ve Tabaka Ürün Etiketleri Baskısı')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Kurumsal Kimlik (Kartvizit, Cepli Dosya, Zarf, Bloknot)')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Kitap, Bülten ve Promosyon Takvim Baskısı')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Varak Yaldız, Gofre ve Kısmi UV Lak Sonlandırma')),
                        array('@type' => 'Offer', 'itemOffered' => array('@type' => 'Service', 'name' => 'Yüksek Tirajlı Fason Ofset Baskı Hizmeti'))
                    )
                ),
                'aggregateRating' => array(
                    '@type'       => 'AggregateRating',
                    'ratingValue' => '4.9',
                    'reviewCount' => '128',
                    'bestRating'  => '5',
                    'worstRating' => '1'
                )
            ),

            // 2. Web Sitesi ve Sitelinks Arama (WebSite & SearchAction)
            array(
                '@type'           => 'WebSite',
                '@id'             => 'https://www.ekizmatbaa.com/#website',
                'url'             => 'https://www.ekizmatbaa.com/',
                'name'            => 'Ekiz Matbaa',
                'publisher'       => array('@id' => 'https://www.ekizmatbaa.com/#printshop'),
                'inLanguage'      => 'tr-TR',
                'potentialAction' => array(
                    '@type'       => 'SearchAction',
                    'target'      => 'https://www.ekizmatbaa.com/?s={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                )
            ),

            // 3. SSS Şeması (FAQPage) - Yapay Zeka Ajanları ve Google Zengin Sonuçları İçin
            array(
                '@type'      => 'FAQPage',
                '@id'        => 'https://www.ekizmatbaa.com/#faq',
                'mainEntity' => array(
                    array(
                        '@type'          => 'Question',
                        'name'           => 'Ekiz Matbaa hangi ürünlerin üretimini yapmaktadır?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => 'Ekiz Matbaa; lüks mukavva sıvamalı sert kutular, karton ambalaj, kozmetik ve gıda kutuları, kurumsal katalog, broşür, karton alışveriş çantaları, rulo/tabaka etiket, cepli dosya, küp bloknot ve kurumsal ofset matbaa ürünlerinin doğrudan üreticisidir.'
                        )
                    ),
                    array(
                        '@type'          => 'Question',
                        'name'           => 'Ofset baskı ve kutu üretiminde minimum sipariş miktarı (tiraj) nedir?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => 'Ofset baskıda kalıp ve makine hazırlık maliyetleri bulunduğundan, genellikle 500 veya 1.000 adet ve üzeri siparişlerde en ekonomik birim fiyat avantajı sağlanır. Projenizin detaylarına göre esnek adetler için teklif alabilirsiniz.'
                        )
                    ),
                    array(
                        '@type'          => 'Question',
                        'name'           => 'Üretim ve teslimat süresi ne kadardır?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => 'Standart katalog, broşür ve kurumsal matbaa işleri onaydan sonra 3-5 iş günü içerisinde tamamlanır. Özel bıçaklı, gofreli, varak yaldızlı ve mukavva sıvamalı lüks ambalaj kutularında ise teslimat süresi adet ve işçilik detayına göre 7-10 iş günüdür. Acil siparişleriniz için ekspres imalat opsiyonumuz bulunmaktadır.'
                        )
                    ),
                    array(
                        '@type'          => 'Question',
                        'name'           => 'Baskı öncesinde numune veya prova hazırlanıyor mu?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => 'Evet, seri üretime geçilmeden önce müşterilerimize dijital renk provası veya özel kutu işlerinde birebir ebatlı maket/numune hazırlanarak onay alınır. Böylece renk, ebat ve kesim hatalarının tamamen önüne geçilir.'
                        )
                    ),
                    array(
                        '@type'          => 'Question',
                        'name'           => 'İstanbul dışına ve yurt dışına gönderim yapıyor musunuz?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => "Evet, İstanbul içinde kendi araçlarımızla, Türkiye'nin 81 iline ise anlaşmalı kargo ve ambar şirketleri ile güvenli sevkiyat yapıyoruz. Ayrıca başta Avrupa olmak üzere yurt dışına gümrüklü ihracat sevkiyatımız mevcuttur."
                        )
                    ),
                    array(
                        '@type'          => 'Question',
                        'name'           => 'Ekiz Matbaa\'dan nasıl hızlı fiyat teklifi alabilirim?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => 'Web sitemizdeki online teklif formunu doldurarak, info@ekizmatbaa.com adresine e-posta göndererek ya da 0212 612 40 53 ve 0536 393 93 06 numaralı telefon ve WhatsApp hatlarımızdan doğrudan iletişime geçerek projeniz için anında özel fiyat teklifi alabilirsiniz.'
                        )
                    )
                )
            )
        )
    );

    echo "\n" . '<!-- Ekiz Matbaa - Google SEO & AI Knowledge Graph Schema -->' . "\n";
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

// --------------------------------------------------------------------------
// 7. COĞRAFİ KONUM VE YAPAY ZEKA DOKÜMANTASYON META ETİKETLERİ
// --------------------------------------------------------------------------
add_action('wp_head', 'ekiz_inject_geo_and_ai_meta', 3);
function ekiz_inject_geo_and_ai_meta() {
    ?>
    <!-- Coğrafi Konumlandırma & Yerel SEO (Geo-tagging) -->
    <meta name="geo.region" content="TR-34" />
    <meta name="geo.placename" content="Eyüpsultan, İstanbul" />
    <meta name="geo.position" content="41.038402;28.922808" />
    <meta name="ICBM" content="41.038402, 28.922808" />
    
    <!-- Yapay Zeka Ajanları İçin Açık Standart (LLMs.txt Manifest) -->
    <link rel="help" type="text/plain" href="<?php echo esc_url(home_url('/llms.txt')); ?>" title="LLMs.txt AI Documentation" />
    <?php
}

// --------------------------------------------------------------------------
// 8. ROBOTS.TXT DİNAMİK YAPAY ZEKA VE ARAMA MOTORU İZİNLERİ
// --------------------------------------------------------------------------
add_filter('robots_txt', 'ekiz_filter_robots_txt_rules', 99, 2);
function ekiz_filter_robots_txt_rules($output, $public) {
    $ai_rules = "\n# AI Search & Retrieval Bots (Generative Engine Optimization)\n";
    $bots = array('GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended', 'cohere-ai', 'Meta-ExternalAgent', 'Amazonbot');
    foreach ($bots as $bot) {
        $ai_rules .= "User-agent: " . $bot . "\nAllow: /\n\n";
    }
    $ai_rules .= "Sitemap: " . esc_url(home_url('/sitemap_index.xml')) . "\n";
    $ai_rules .= "llms.txt: " . esc_url(home_url('/llms.txt')) . "\n";
    $ai_rules .= "llms-full.txt: " . esc_url(home_url('/llms-full.txt')) . "\n";

    return $output . $ai_rules;
}

// --------------------------------------------------------------------------
// 9. GOOGLE WORKSPACE (GMAIL) SMTP E-POSTA ENTEGRASYONU
// --------------------------------------------------------------------------
add_action('phpmailer_init', 'ekiz_configure_google_workspace_smtp', 9999);
function ekiz_configure_google_workspace_smtp($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.gmail.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->Username   = 'info@ekizmatbaa.com';
    $phpmailer->Password   = 'qxeujvbplnlkddsu';
    $phpmailer->From       = 'info@ekizmatbaa.com';
    $phpmailer->FromName   = 'Ekiz Matbaa';
}

add_filter('wp_mail_from', function($email) {
    return 'info@ekizmatbaa.com';
}, 9999);

add_filter('wp_mail_from_name', function($name) {
    return 'Ekiz Matbaa';
}, 9999);



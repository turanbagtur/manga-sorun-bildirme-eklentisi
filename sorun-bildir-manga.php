<?php
/**
 * Plugin Name: Manga Sorun Bildirme Eklentisi
 * Plugin URI:  https://mangaruhu.com
 * Description: Manga siteniz için kullanıcıların sorun bildirmesini sağlayan basit bir eklenti.
 * Version:     1.4
 * Author:      Solderet x Gemini
 * Author URI:  https://mangaruhu.com
 * Text Domain: sorun-bildir-manga
 */

// Doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Eklentiyi etkinleştirdiğinizde çalışacak fonksiyon
register_activation_hook( __FILE__, 'sbm_aktivasyon' );

function sbm_aktivasyon() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $table_name = $wpdb->prefix . 'manga_sorunlari';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        manga_adi varchar(255) NOT NULL,
        sorun_aciklamasi text NOT NULL,
        sorun_turu varchar(255) NOT NULL,
        kullanici_ip varchar(45) NOT NULL,
        kullanici_adi varchar(255) DEFAULT 'Misafir',
        manga_url varchar(255) NOT NULL,
        durum varchar(50) NOT NULL DEFAULT 'bekliyor',
        olusturma_tarihi datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        discord_mesaj_id varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta( $sql );
}

// Admin menüsü ve ayarlar sayfası
add_action( 'admin_menu', 'sbm_admin_menusu' );

function sbm_admin_menusu() {
    add_menu_page(
        'Manga Sorun Bildirimleri',
        'Sorun Bildirimi',
        'manage_options',
        'manga-sorun-bildirimleri',
        'sbm_sorun_bildirimleri_sayfasi',
        'dashicons-warning',
        6
    );
    add_submenu_page(
        'manga-sorun-bildirimleri',
        'Eklenti Ayarları',
        'Ayarlar',
        'manage_options',
        'manga-sorun-ayarlari',
        'sbm_ayarlar_sayfasi'
    );
}

// Eklenti Ayarları sayfası
function sbm_ayarlar_sayfasi() {
    ?>
    <div class="wrap">
        <h1>Manga Sorun Bildirme Eklentisi Ayarları</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'sbm_ayarlar_grubu' ); ?>
            <?php do_settings_sections( 'sbm_ayarlar_grubu' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Discord Webhook URL</th>
                    <td><input type="text" name="sbm_discord_webhook" value="<?php echo esc_attr( get_option( 'sbm_discord_webhook' ) ); ?>" style="width: 100%;" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Ayarları kaydetme fonksiyonu
add_action( 'admin_init', 'sbm_ayarlari_kaydet' );

function sbm_ayarlari_kaydet() {
    register_setting( 'sbm_ayarlar_grubu', 'sbm_discord_webhook' );
}

// Yönetici paneli sayfası
function sbm_sorun_bildirimleri_sayfasi() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'manga_sorunlari';
    
    // Durum güncelleme işlemi
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'sbm_durum_guncelle' && isset( $_GET['id'] ) && isset( $_GET['durum'] ) ) {
        if ( wp_verify_nonce( $_GET['_wpnonce'], 'sbm_durum_guncelle_' . $_GET['id'] ) ) {
            $id = intval( $_GET['id'] );
            $durum = sanitize_text_field( $_GET['durum'] );
            
            sbm_discord_mesaj_guncelle( $id, $durum );

            $wpdb->update(
                $table_name,
                array( 'durum' => $durum ),
                array( 'id' => $id )
            );
            
            echo '<div class="notice notice-success is-dismissible"><p>Durum başarıyla güncellendi.</p></div>';
        }
    }
    
    // Silme işlemi
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'sbm_sil' && isset( $_GET['id'] ) ) {
        if ( wp_verify_nonce( $_GET['_wpnonce'], 'sbm_sil_' . $_GET['id'] ) ) {
            $id = intval( $_GET['id'] );
            
            sbm_discord_mesaj_sil( $id );
            
            $wpdb->delete( $table_name, array( 'id' => $id ) );
            
            echo '<div class="notice notice-success is-dismissible"><p>Sorun bildirimi başarıyla silindi.</p></div>';
        }
    }

    $sorunlar = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY olusturma_tarihi DESC", ARRAY_A );
    ?>
    <div class="wrap">
        <h1>Manga Sorun Bildirimleri</h1>
        <?php if ( ! empty( $sorunlar ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Manga/Bölüm Adı</th>
                        <th>Sorun Türü</th>
                        <th>Açıklama</th>
                        <th>Kullanıcı</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>Eylemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $sorunlar as $sorun ) : ?>
                        <tr>
                            <td><?php echo esc_html( $sorun['id'] ); ?></td>
                            <td><a href="<?php echo esc_url($sorun['manga_url']); ?>" target="_blank"><?php echo esc_html( $sorun['manga_adi'] ); ?></a></td>
                            <td><?php echo esc_html( $sorun['sorun_turu'] ); ?></td>
                            <td><?php echo esc_html( $sorun['sorun_aciklamasi'] ); ?></td>
                            <td><?php echo esc_html( $sorun['kullanici_adi'] ); ?></td>
                            <td>
                                <span class="sbm-durum-<?php echo esc_attr( $sorun['durum'] ); ?>">
                                    <?php echo esc_html( ucfirst( $sorun['durum'] ) ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( $sorun['olusturma_tarihi'] ); ?></td>
                            <td>
                                <?php
                                $nonce_inceleniyor = wp_create_nonce( 'sbm_durum_guncelle_' . $sorun['id'] );
                                $nonce_cozuldu = wp_create_nonce( 'sbm_durum_guncelle_' . $sorun['id'] );
                                $nonce_sil = wp_create_nonce( 'sbm_sil_' . $sorun['id'] );

                                // Yalnızca durum "bekliyor" ise İnceleniyor ve Çözüldü butonlarını göster
                                if ( $sorun['durum'] === 'bekliyor' ) {
                                    echo '<a href="' . esc_url( admin_url( 'admin.php?page=manga-sorun-bildirimleri&action=sbm_durum_guncelle&id=' . $sorun['id'] . '&durum=inceleniyor&_wpnonce=' . $nonce_inceleniyor ) ) . '" class="button">İnceleniyor</a> ';
                                    echo '<a href="' . esc_url( admin_url( 'admin.php?page=manga-sorun-bildirimleri&action=sbm_durum_guncelle&id=' . $sorun['id'] . '&durum=cozuldu&_wpnonce=' . $nonce_cozuldu ) ) . '" class="button button-primary">Çözüldü</a> ';
                                } elseif ( $sorun['durum'] === 'inceleniyor' ) {
                                    echo '<a href="' . esc_url( admin_url( 'admin.php?page=manga-sorun-bildirimleri&action=sbm_durum_guncelle&id=' . $sorun['id'] . '&durum=cozuldu&_wpnonce=' . $nonce_cozuldu ) ) . '" class="button button-primary">Çözüldü</a> ';
                                }
                                ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=manga-sorun-bildirimleri&action=sbm_sil&id=' . $sorun['id'] . '&_wpnonce=' . $nonce_sil ) ); ?>" class="button button-secondary" onclick="return confirm('Bu sorun bildirimini silmek istediğinizden emin misiniz?');">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Şu anda bildirilen bir sorun bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Frontend ve backend scriptlerini çağırma
add_action( 'wp_enqueue_scripts', 'sbm_frontend_scriptleri' );
add_action( 'admin_enqueue_scripts', 'sbm_backend_scriptleri' );

function sbm_frontend_scriptleri() {
    wp_enqueue_style( 'sbm-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.3' );
    wp_enqueue_script( 'sbm-script', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), '1.3', true );
    
    wp_localize_script( 'sbm-script', 'sbm_ajax_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'sbm_nonce' )
    ) );
}

function sbm_backend_scriptleri() {
    wp_enqueue_style( 'sbm-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', array(), '1.3' );
}

// AJAX ile form verilerini işleme
add_action( 'wp_ajax_sbm_sorun_bildir', 'sbm_sorun_bildir_callback' );
add_action( 'wp_ajax_nopriv_sbm_sorun_bildir', 'sbm_sorun_bildir_callback' );

function sbm_sorun_bildir_callback() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sbm_nonce' ) ) {
        wp_send_json_error( 'Nonce doğrulama hatası!' );
    }

    $manga_adi = sanitize_text_field( $_POST['manga_adi'] );
    $manga_url = sanitize_url( $_POST['manga_url'] );
    $sorun_turu = sanitize_text_field( $_POST['sorun_turu'] );
    $aciklama = sanitize_textarea_field( $_POST['aciklama'] );
    $kullanici_adi = sanitize_text_field( $_POST['kullanici_adi'] ) ?: 'Misafir';

    if ( is_user_logged_in() ) {
        $kullanici_adi = wp_get_current_user()->display_name;
    }

    if ( empty( $manga_adi ) || empty( $sorun_turu ) ) {
        wp_send_json_error( 'Lütfen tüm gerekli alanları doldurun.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'manga_sorunlari';
    
    $wpdb->insert(
        $table_name,
        array(
            'manga_adi' => $manga_adi,
            'sorun_turu' => $sorun_turu,
            'sorun_aciklamasi' => $aciklama,
            'kullanici_ip' => $_SERVER['REMOTE_ADDR'],
            'kullanici_adi' => $kullanici_adi,
            'manga_url' => $manga_url
        )
    );

    $insert_id = $wpdb->insert_id;

    if ( $wpdb->last_error ) {
        wp_send_json_error( 'Veritabanına kaydederken bir hata oluştu.' );
    } else {
        $discord_mesaj_id = sbm_discord_mesaj_gonder( $insert_id, $manga_adi, $manga_url, $sorun_turu, $aciklama, $kullanici_adi );
        
        if ($discord_mesaj_id) {
            $wpdb->update(
                $table_name,
                array('discord_mesaj_id' => $discord_mesaj_id),
                array('id' => $insert_id)
            );
        }

        wp_send_json_success( 'Sorun bildiriminiz başarıyla iletilmiştir. Teşekkür ederiz!' );
    }
}

// Discord'a yeni mesaj gönderme fonksiyonu
function sbm_discord_mesaj_gonder($id, $manga_adi, $manga_url, $sorun_turu, $aciklama, $kullanici_adi) {
    $webhook_url = get_option('sbm_discord_webhook');
    if ( empty($webhook_url) ) { return false; }

    $aciklama = empty($aciklama) ? 'Açıklama girilmedi.' : $aciklama;
    $site_adi = get_bloginfo('name');
    
    $payload = array(
        'username' => 'Manga Sorun Bildirimi',
        'embeds' => array(
            array(
                'title' => 'Yeni Sorun Bildirimi (#'.$id.')',
                'color' => 15158332, // Kırmızı
                'fields' => array(
                    array(
                        'name' => 'Manga/Bölüm Adı',
                        'value' => '[' . $manga_adi . '](' . $manga_url . ')',
                        'inline' => false
                    ),
                    array(
                        'name' => 'Sorun Türü',
                        'value' => $sorun_turu,
                        'inline' => false
                    ),
                    array(
                        'name' => 'Açıklama',
                        'value' => $aciklama,
                        'inline' => false
                    ),
                    array(
                        'name' => 'Bildiren Kullanıcı',
                        'value' => $kullanici_adi,
                        'inline' => false
                    ),
                    array(
                        'name' => 'Durum',
                        'value' => 'Bekliyor',
                        'inline' => false
                    )
                ),
                'footer' => array('text' => 'Gönderim Yeri: '.$site_adi),
                'timestamp' => date('c')
            )
        )
    );

    $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    $response = wp_remote_post($webhook_url, array(
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'        => $json_payload,
        'method'      => 'POST',
        'data_format' => 'body'
    ));

    if ( is_wp_error( $response ) ) {
        error_log('Discord Webhook Hatası: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $discord_response = json_decode($body);
    return $discord_response->id ?? null;
}

// Discord'daki mevcut mesajı güncelleme fonksiyonu
function sbm_discord_mesaj_guncelle($id, $yeni_durum) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'manga_sorunlari';
    
    $sorun = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A );
    
    $webhook_url = get_option('sbm_discord_webhook');
    if ( empty($webhook_url) || empty($sorun['discord_mesaj_id']) ) { return false; }

    $aciklama = empty($sorun['sorun_aciklamasi']) ? 'Açıklama girilmedi.' : $sorun['sorun_aciklamasi'];
    $renk = 15158332; // Kırmızı (Bekliyor)
    if ($yeni_durum === 'inceleniyor') {
        $renk = 16776960; // Turuncu
    } elseif ($yeni_durum === 'cozuldu') {
        $renk = 3066993; // Yeşil
    }

    $payload = array(
        'username' => 'Manga Sorun Bildirimi',
        'embeds' => array(
            array(
                'title' => 'Sorun Bildirimi (#'.$sorun['id'].') - Durum Güncellendi',
                'color' => $renk,
                'fields' => array(
                    array('name' => 'Manga/Bölüm Adı', 'value' => '[' . $sorun['manga_adi'] . '](' . $sorun['manga_url'] . ')', 'inline' => false),
                    array('name' => 'Sorun Türü', 'value' => $sorun['sorun_turu'], 'inline' => false),
                    array('name' => 'Açıklama', 'value' => $aciklama, 'inline' => false),
                    array('name' => 'Bildiren Kullanıcı', 'value' => $sorun['kullanici_adi'], 'inline' => false),
                    array('name' => 'Durum', 'value' => ucfirst($yeni_durum), 'inline' => false)
                ),
                'footer' => array('text' => 'Bu mesaj otomatik olarak sitenizden güncellenmiştir.'),
                'timestamp' => date('c')
            )
        )
    );

    $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    wp_remote_post( $webhook_url . '/messages/' . $sorun['discord_mesaj_id'], array(
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'        => $json_payload,
        'method'      => 'PATCH',
        'data_format' => 'body'
    ));
}

// Discord'daki mesajı silme fonksiyonu
function sbm_discord_mesaj_sil($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'manga_sorunlari';
    
    $sorun = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A );
    
    $webhook_url = get_option('sbm_discord_webhook');
    if ( empty($webhook_url) || empty($sorun['discord_mesaj_id']) ) { return false; }

    wp_remote_request( $webhook_url . '/messages/' . $sorun['discord_mesaj_id'], array(
        'method' => 'DELETE',
    ));
}

// Sorun bildirim formunu gösteren shortcode
add_shortcode( 'sorun_bildir_formu', 'sbm_sorun_bildirim_shortcode' );

function sbm_sorun_bildirim_shortcode() {
    if ( ! is_single() ) {
        return '';
    }
    
    $manga_adi = get_the_title();
    $manga_url = get_permalink();
    $kullanici_adi = is_user_logged_in() ? wp_get_current_user()->display_name : '';

    ob_start();
    ?>
    <button id="sbm-open-form" class="sbm-button">Sorun Bildir</button>
    <div id="sbm-form-modal" class="sbm-modal">
        <div class="sbm-modal-content">
            <span id="sbm-close-form" class="sbm-close">&times;</span>
            <h3>Sorun Bildir</h3>
            <form id="sbm-sorun-formu">
                <p>
                    <label for="manga_adi">Manga/Bölüm Adı:</label>
                    <input type="text" id="manga_adi" name="manga_adi" value="<?php echo esc_attr($manga_adi); ?>" readonly>
                </p>
                <?php if ( ! is_user_logged_in() ) : ?>
                <p>
                    <label for="kullanici_adi">Adınız (isteğe bağlı):</label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" placeholder="Misafir">
                </p>
                <?php else: ?>
                <input type="hidden" id="kullanici_adi" name="kullanici_adi" value="<?php echo esc_attr($kullanici_adi); ?>">
                <?php endif; ?>
                <input type="hidden" name="manga_url" value="<?php echo esc_url($manga_url); ?>">
                <p>
                    <label for="sorun_turu">Sorun Türü:</label>
                    <select id="sorun_turu" name="sorun_turu" required>
                        <option value="">Seçiniz...</option>
                        <option value="Bölüm açılmıyor">Bölüm açılmıyor</option>
                        <option value="Yanlış bölüm">Yanlış bölüm</option>
                        <option value="Eksik sayfalar">Eksik sayfalar</option>
                        <option value="Çeviri hatası">Çeviri hatası</option>
                        <option value="Sayfalar karışmış">Sayfalar karışmış</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </p>
                <p>
                    <label for="aciklama">Açıklama (isteğe bağlı):</label>
                    <textarea id="aciklama" name="aciklama" rows="4"></textarea>
                </p>
                <input type="submit" value="Gönder" class="button button-primary">
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
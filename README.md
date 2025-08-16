Manga Sorun Bildirim Sistemi (WordPress Eklentisi)
<p>Manga ve webtoon siteleri için geliştirilmiş, okuyucuların bölümlerdeki hataları kolayca bildirmesini sağlayan bir WordPress eklentisidir. Gelişmiş Discord entegrasyonu sayesinde tüm bildirimler anında Discord kanalınıza düşer.</p>

<h3>Özellikler</h3>
<ul>
<li><strong>Hızlı ve Şık Arayüz:</strong> Kullanıcılar tek tıkla açılan şık bir form üzerinden sorunları kolayca bildirir.</li>
<li><strong>Discord Entegrasyonu:</strong> Tüm bildirimler gerçek zamanlı olarak Discord'a iletilir.</li>
<li><strong>Akıllı Durum Güncelleme:</strong> Yönetici panelinden bildirim durumunu güncellediğinizde (inceleniyor, çözüldü), Discord'daki mesaj da otomatik olarak değişir.</li>
<li><strong>Misafir Desteği:</strong> Siteye üye olmayan kullanıcılar bile sorun bildirebilir.</li>
<li><strong>Basit Yönetim:</strong> WordPress paneli üzerinden tüm bildirimleri kolayca yönetebilir ve silebilirsiniz.</li>
</ul>

<h3>Kurulum</h3>

<h4>1. Eklentiyi Kurun</h4>
<ol>
<li>Bu repository'yi ZIP dosyası olarak indirin.</li>
<li>WordPress admin panelinde <strong>Eklentiler > Yeni Ekle > Eklenti Yükle</strong> adımlarını izleyerek indirdiğiniz ZIP dosyasını yükleyin ve etkinleştirin.</li>
</ol>

<h4>2. Discord Ayarını Yapın</h4>
<ol>
<li>Discord'da bildirimler için bir kanal oluşturun ve bu kanalın Webhook URL'sini alın.</li>
<li>WordPress admin panelinde <strong>Sorun Bildirimi > Ayarlar</strong> sayfasına giderek bu URL'yi yapıştırın ve kaydedin.</li>
</ol>

<h4>3. Butonu Ekleyin</h4>
<p>Manga bölümlerinizin gösterildiği tema dosyanıza (<code>single.php</code> veya ilgili bir şablon dosyası), butonun görünmesini istediğiniz yere aşağıdaki kodu ekleyin:</p>

<pre><code>&lt;?php echo do_shortcode('[sorun_bildir_formu]'); ?&gt;</code></pre>

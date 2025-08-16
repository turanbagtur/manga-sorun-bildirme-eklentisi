<h1>📢 Manga/Webtoon Sorun Bildirim Sistemi</h1>

<p>Web siteniz için kullanabileceğiniz, <strong>manga/webtoon sorun bildirim sistemi</strong> artık açık kaynak olarak paylaşılıyor. 
Eklentinin amacı, okuyucularınızın karşılaştığı sorunları size anında ulaştırmak ve yönetimini kolaylaştırmaktır.</p>

<h2>✨ Öne Çıkan Özellikler</h2>
<ul>
  <li><strong>Hızlı ve Şık Arayüz:</strong> Okuyucular tek bir butona tıklayarak sorunu anında size iletebilir.</li>
  <li><strong>Discord Entegrasyonu:</strong> Her bildirim, otomatik olarak Discord kanalınıza düşer. Yönetici panelinde durum güncellendiğinde (inceleniyor, çözüldü) Discord mesajı da eşzamanlı olarak güncellenir.</li>
  <li><strong>Tam Kontrol:</strong> WordPress admin paneliniz üzerinden tüm bildirimleri görüntüleyebilir, durumlarını yönetebilir ve silebilirsiniz.</li>
  <li><strong>Misafir Desteği:</strong> Üye olmayan okuyucular bile kolayca sorun bildirebilir.</li>
</ul>

<h2>⚙️ Kurulum ve Ayarlar (3 Adımda Tamamlayın!)</h2>

<h3>1. Eklentiyi Kurun</h3>
<ul>
  <li>Eklentiyi aşağıdaki linkten indirin:</li>
  <p><a href="[Eklenti İndirme Linki]https://github.com/turanbagtur/manga-sorun-bildirme-eklentisi/releases/tag/sorunbildirim-v1.4">📥 Eklenti İndirme Linki</a></p>
  <li>WordPress admin panelinize gidin ve <strong>Eklentiler > Yeni Ekle > Eklenti Yükle</strong> adımlarını takip ederek indirdiğiniz ZIP dosyasını yükleyin ve etkinleştirin.</li>
</ul>

<h3>2. Discord Webhook Ayarı</h3>
<ul>
  <li>Discord’da sorun bildirimleri için yeni bir kanal oluşturun.</li>
  <li>Kanal ayarlarına giderek bir Webhook oluşturun ve URL’sini kopyalayın.</li>
  <li>WordPress admin panelinde <strong>Sorun Bildirimi > Ayarlar</strong> sayfasına giderek kopyaladığınız Webhook URL’sini yapıştırın ve kaydedin.</li>
</ul>

<h3>3. Butonu Ekleyin</h3>
<ul>
  <li>Manga bölümlerinizin görüntülendiği tema dosyasını açın (<code>single.php</code> veya benzeri).</li>
  <li><strong>Mangareader teması</strong> için yol: <code>template-parts/single/single-advanced.php</code></li>
  <li>Okuma alanının altına veya istediğiniz herhangi bir yere şu kodu ekleyin:</li>
</ul>

<pre><code>&lt;?php echo do_shortcode('[sorun_bildir_formu]'); ?&gt;
</code></pre>

<h2>🎉 Hepsi Bu Kadar!</h2>
<p>Artık okuyucularınızdan gelen bildirimleri otomatik olarak Discord’unuzda görebilirsiniz. Sorularınız veya geri bildirimleriniz için bize ulaşmaktan çekinmeyin.</p>

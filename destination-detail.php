<?php
$page_title = 'Destination Details - Serendib Pathways';
require_once 'config/database.php';

$db = (new Database())->getConnection();
$destination_id = isset($_GET['id']) ? max(0, (int) $_GET['id']) : 0;
$stmt = $db->prepare('SELECT d.*, c.category_name FROM destinations d LEFT JOIN categories c ON d.category_id=c.category_id WHERE d.id=?');
$stmt->execute([$destination_id]);
$destination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$destination) {
    header('Location: destinations.php');
    exit;
}

$page_title = $destination['name'] . ' Travel Guide | Serendib Pathways';
$page_description = mb_substr(trim(strip_tags((string) $destination['description'])), 0, 155);
$page_image = $destination['image'] ?: 'assets/serendib-pathways-horizontal.png';
$extra_stylesheet = 'assets/css/destination-detail.css?v=2';
$extra_stylesheet_secondary = 'assets/css/destination-meaning.css?v=1';
$gallery = array_values(array_filter([
    $destination['image'], $destination['gallery_image1'], $destination['gallery_image2'],
    $destination['gallery_image3'], $destination['gallery_image4'],
]));
$gallery = array_values(array_unique($gallery));
$highlight_text = trim((string) ($destination['highlights'] ?? ''));
$highlights = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $highlight_text))));
$place_guides = [
 'Sigiriya'=>['label'=>'Cultural significance','meaning'=>'Sigiriya preserves an extraordinary fifth-century royal centre built around a natural rock fortress. Its formal gardens, hydraulic engineering, paintings and summit ruins reveal remarkable artistic and technical ambition. The landscape also carries a longer monastic history, making it culturally layered rather than simply a palace on a rock.','experience'=>'Arrive near opening time, move slowly through the water gardens and pause at the elevated terraces before climbing. The summit brings the architecture and surrounding plains into one unforgettable view.','respect'=>'Wear secure footwear, carry water and follow directional signs. Photography restrictions may apply near ancient paintings; observe posted guidance and avoid touching archaeological surfaces.'],
 'Bentota'=>['label'=>'Coastal character','meaning'=>'Bentota is shaped by the meeting of river, lagoon and Indian Ocean. Its importance is experiential rather than sacred: fishing life, water-based travel, tropical gardens and a long sweep of coast create one of the southwest island’s classic places to slow down.','experience'=>'Balance quiet beach time with an early river journey, when the water is calmer and birdlife is active. Leave space for an unhurried sunset rather than filling every hour.','respect'=>'Choose responsible boat operators, avoid disturbing wildlife and keep plastic away from the beach and waterways. Swim only where local safety advice permits.'],
 'Temple of the Sacred Tooth Relic'=>['label'=>'Sacred significance','meaning'=>'This temple is revered because it houses the Sacred Tooth Relic of the Buddha. Across Sri Lankan history, guardianship of the relic became closely connected with sovereignty and protection of the Buddhist tradition. Today it remains a living place of worship, with daily offerings, drumming and devotion at the heart of Kandy.','experience'=>'Visit during a puja to hear ceremonial drums and witness devotees offering flowers. The experience is most meaningful when approached patiently as worship rather than as a performance.','respect'=>'Cover shoulders and knees, remove shoes and hats, and keep your voice low. Never pose with your back toward a Buddha image or obstruct worshippers making offerings.'],
 'Diva Guhawa'=>['label'=>'Sacred tradition','meaning'=>'Diva Guhawa belongs to the devotional landscape associated with Sri Pada. Buddhist tradition connects the cave with the Buddha’s visit to the sacred mountain, giving this modest sanctuary significance far beyond its physical scale. It is best understood through faith, pilgrimage and local memory.','experience'=>'Allow time for quiet observation of the cave shrine and its mountain setting. A knowledgeable local guide can help distinguish devotional tradition, later artistic additions and the wider Sri Pada pilgrimage story.','respect'=>'Dress modestly, remove footwear where requested and avoid loud conversation. Ask before photographing people, rituals or the interior of a shrine.'],
 'Dunhinda Falls'=>['label'=>'Natural significance','meaning'=>'Dunhinda is celebrated for the veil of spray that gathers around its forested gorge—the mist-like effect reflected in its name. It is not presented as a major sacred site; its power comes from water, rock, forest and the dramatic reveal at the end of the trail.','experience'=>'Walk in the cooler morning hours and pause at designated viewpoints. After rain the waterfall is especially powerful, though the path may also be more slippery.','respect'=>'Stay behind safety barriers, wear shoes with grip and carry every item of waste back with you. Do not enter dangerous currents or climb wet rocks for photographs.'],
 'Mahiyanganaya'=>['label'=>'Sacred significance','meaning'=>'Mahiyanganaya is one of Sri Lanka’s deeply revered Buddhist landscapes. According to Buddhist tradition, the Buddha visited this region on his first journey to the island. The Mahiyangana stupa is connected with the earliest layer of the island’s Buddhist sacred geography, while the wider region also opens a window onto rural life and indigenous Vedda heritage.','experience'=>'Visit the sacred precinct in the softer morning or evening light, then explore the wider region with someone able to explain both Buddhist tradition and local community history respectfully.','respect'=>'Dress modestly within temple grounds, remove shoes and hats, and never treat indigenous communities as staged attractions. Seek consent before photography and support community-led experiences.'],
 'Muthiyangana Raja Maha Vihara'=>['label'=>'Sacred significance','meaning'=>'Muthiyangana is traditionally counted among the Solosmasthana—the sixteen places in Sri Lanka revered as having been visited by the Buddha. That belief makes the temple one of Badulla’s most important devotional spaces. Its stupa, shrines and long continuity of worship connect visitors with generations of Buddhist practice.','experience'=>'Walk through the precinct without rushing, observing the relationship between the stupa, shrine rooms, offerings and mature temple landscape. Early morning offers a particularly calm atmosphere.','respect'=>'Wear clothing that covers shoulders and knees, remove shoes and hats, and walk around sacred structures respectfully. Avoid intrusive photography during worship.'],
];
$place_guide = $place_guides[$destination['name']] ?? null;

include 'includes/header.php';
?>

<section class="destination-showcase" style="--destination-image:url('<?= htmlspecialchars($destination['image'], ENT_QUOTES) ?>')">
  <div class="destination-showcase__shade"></div>
  <div class="catalog-shell destination-showcase__content">
    <nav class="destination-crumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span>›</span><a href="destinations.php">Destinations</a><span>›</span><strong><?= htmlspecialchars($destination['name']) ?></strong></nav>
    <div class="destination-kicker"><span></span><?= htmlspecialchars($destination['category_name'] ?: 'Sri Lanka') ?></div>
    <h1><?= htmlspecialchars($destination['name']) ?></h1>
    <p><?= htmlspecialchars($destination['location'] ?: 'Sri Lanka') ?></p>
    <div class="destination-hero-actions">
      <a class="destination-primary-action" href="contact.php?destination=<?= urlencode($destination['name']) ?>">Plan this journey <i class="fa-solid fa-arrow-right"></i></a>
      <button type="button" class="destination-glass-action" onclick="openGeminiChat()"><i class="fa-solid fa-wand-magic-sparkles"></i> Ask Nila</button>
    </div>
  </div>
  <a class="destination-scroll-cue" href="#destination-story" aria-label="Explore destination details"><span>Explore</span><i class="fa-solid fa-arrow-down"></i></a>
</section>

<section class="destination-story" id="destination-story">
  <div class="catalog-shell destination-story__grid">
    <div class="destination-story__intro">
      <span class="destination-eyebrow">WHY GO</span>
      <h2>A place that stays<br>with <em>you.</em></h2>
    </div>
    <div class="destination-story__copy">
      <p><?= nl2br(htmlspecialchars($destination['description'])) ?></p>
      <div class="destination-facts">
        <div><i class="fa-solid fa-location-dot"></i><span>Region</span><strong><?= htmlspecialchars($destination['location'] ?: 'Sri Lanka') ?></strong></div>
        <div><i class="fa-solid fa-compass"></i><span>Experience</span><strong><?= htmlspecialchars($destination['category_name'] ?: 'Discovery') ?></strong></div>
        <div><i class="fa-regular fa-images"></i><span>Gallery</span><strong><?= count($gallery) ?> views</strong></div>
      </div>
    </div>
  </div>
</section>

<?php if ($place_guide): ?>
<section class="destination-meaning-section">
  <div class="catalog-shell">
    <div class="destination-section-heading"><div><span><?= htmlspecialchars(strtoupper($place_guide['label'])) ?></span><h2>Understand the<br><em>place.</em></h2></div><p>Travel feels richer when the story, living meaning and local customs come with the view.</p></div>
    <div class="destination-meaning-grid">
      <article class="destination-meaning-card destination-meaning-card--lead"><span>01</span><i class="fa-solid fa-landmark-dome"></i><h3>Why it matters</h3><p><?= htmlspecialchars($place_guide['meaning']) ?></p></article>
      <article class="destination-meaning-card"><span>02</span><i class="fa-regular fa-compass"></i><h3>How to experience it</h3><p><?= htmlspecialchars($place_guide['experience']) ?></p></article>
      <article class="destination-meaning-card"><span>03</span><i class="fa-solid fa-hands-praying"></i><h3>Visit with respect</h3><p><?= htmlspecialchars($place_guide['respect']) ?></p></article>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($gallery): ?>
<section class="destination-gallery-section">
  <div class="catalog-shell">
    <div class="destination-section-heading"><div><span>LOOK CLOSER</span><h2>Scenes from<br><em><?= htmlspecialchars($destination['name']) ?>.</em></h2></div><p>Select any photograph to view it in full.</p></div>
    <div class="destination-gallery destination-gallery--<?= min(count($gallery), 5) ?>">
      <?php foreach ($gallery as $index => $image): ?>
      <button type="button" class="destination-gallery__item destination-gallery__item--<?= $index + 1 ?>" data-gallery-image="<?= htmlspecialchars($image, ENT_QUOTES) ?>" aria-label="Open <?= htmlspecialchars($destination['name']) ?> photograph <?= $index + 1 ?>">
        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($destination['name']) ?> — photograph <?= $index + 1 ?>" loading="<?= $index > 1 ? 'lazy' : 'eager' ?>">
        <span><b><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></b><i class="fa-solid fa-expand"></i></span>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($highlights): ?>
<section class="destination-highlights-section">
  <div class="catalog-shell destination-highlights-grid">
    <div><span class="destination-eyebrow">DON'T MISS</span><h2>Signature<br><em>moments.</em></h2></div>
    <ol>
      <?php foreach ($highlights as $index => $highlight): ?>
      <li><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><p><?= htmlspecialchars($highlight) ?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/destination-hotels.php'; ?>

<section class="destination-related-section">
  <div class="catalog-shell">
    <div class="destination-section-heading"><div><span>KEEP EXPLORING</span><h2>Continue your<br><em>journey.</em></h2></div><a href="destinations.php">View all destinations <i class="fa-solid fa-arrow-right"></i></a></div>
    <div class="destination-related-grid">
      <?php
      $relatedStmt = $db->prepare('SELECT id,name,description,image,location FROM destinations WHERE id<>? ORDER BY RAND() LIMIT 3');
      $relatedStmt->execute([$destination_id]);
      foreach ($relatedStmt->fetchAll(PDO::FETCH_ASSOC) as $related):
      ?>
      <a class="destination-related-card" href="destination-detail.php?id=<?= (int) $related['id'] ?>">
        <img src="<?= htmlspecialchars($related['image']) ?>" alt="<?= htmlspecialchars($related['name']) ?>" loading="lazy">
        <span><?= htmlspecialchars($related['location'] ?: 'Sri Lanka') ?></span>
        <h3><?= htmlspecialchars($related['name']) ?></h3>
        <p><?= htmlspecialchars(mb_substr(strip_tags($related['description']), 0, 105)) ?>…</p>
        <b>Discover this place <i class="fa-solid fa-arrow-right"></i></b>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="destination-final-cta">
  <div class="catalog-shell"><span>YOUR JOURNEY, YOUR WAY</span><h2>Ready to experience<br><em><?= htmlspecialchars($destination['name']) ?>?</em></h2><p>Tell us what moves you. We will shape the route, pace and stays around you.</p><a href="contact.php?destination=<?= urlencode($destination['name']) ?>">Start planning <i class="fa-solid fa-arrow-right"></i></a></div>
</section>

<div class="destination-lightbox" id="destination-lightbox" aria-hidden="true" role="dialog" aria-label="Destination photograph">
  <button type="button" class="destination-lightbox__close" aria-label="Close image"><i class="fa-solid fa-xmark"></i></button>
  <button type="button" class="destination-lightbox__nav destination-lightbox__prev" aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button>
  <figure><img src="" alt=""><figcaption></figcaption></figure>
  <button type="button" class="destination-lightbox__nav destination-lightbox__next" aria-label="Next image"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<script>
(() => {
  const items = [...document.querySelectorAll('[data-gallery-image]')];
  const lightbox = document.querySelector('#destination-lightbox');
  if (!items.length || !lightbox) return;
  const image = lightbox.querySelector('img');
  const caption = lightbox.querySelector('figcaption');
  let active = 0;
  const show = index => { active = (index + items.length) % items.length; image.src = items[active].dataset.galleryImage; image.alt = items[active].getAttribute('aria-label'); caption.textContent = `${active + 1} / ${items.length}`; };
  const open = index => { show(index); lightbox.classList.add('is-open'); lightbox.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; };
  const close = () => { lightbox.classList.remove('is-open'); lightbox.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };
  items.forEach((item, index) => item.addEventListener('click', () => open(index)));
  lightbox.querySelector('.destination-lightbox__close').addEventListener('click', close);
  lightbox.querySelector('.destination-lightbox__prev').addEventListener('click', () => show(active - 1));
  lightbox.querySelector('.destination-lightbox__next').addEventListener('click', () => show(active + 1));
  lightbox.addEventListener('click', event => { if (event.target === lightbox) close(); });
  document.addEventListener('keydown', event => { if (!lightbox.classList.contains('is-open')) return; if (event.key === 'Escape') close(); if (event.key === 'ArrowLeft') show(active - 1); if (event.key === 'ArrowRight') show(active + 1); });
})();
</script>

<?php include 'includes/footer.php'; ?>

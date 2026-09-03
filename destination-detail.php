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
$extra_stylesheet = 'assets/css/destination-detail.css?v=1';
$gallery = array_values(array_filter([
    $destination['image'], $destination['gallery_image1'], $destination['gallery_image2'],
    $destination['gallery_image3'], $destination['gallery_image4'],
]));
$gallery = array_values(array_unique($gallery));
$highlight_text = trim((string) ($destination['highlights'] ?? ''));
$highlights = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $highlight_text))));

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

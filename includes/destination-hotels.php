<?php
$hotelStmt=$db->prepare('SELECT * FROM destination_hotels WHERE destination_id=? ORDER BY sort_order,name');
$hotelStmt->execute([$destination_id]);
$hotels=$hotelStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if($hotels): ?>
<section class="stay-section">
  <div class="catalog-shell">
    <div class="catalog-heading"><div><span>STAY BEAUTIFULLY</span><h2>Handpicked places<br>to call <em>home.</em></h2></div><p>Five considered stays around <?=htmlspecialchars($destination['name'])?>. Ask our team to match the right atmosphere and budget to your journey.</p></div>
    <div class="stay-scroller">
      <?php foreach($hotels as$i=>$hotel): ?><article class="stay-card">
        <div class="stay-no"><?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></div>
        <span class="stay-tier"><?=htmlspecialchars($hotel['price_tier'])?></span>
        <h3><?=htmlspecialchars($hotel['name'])?></h3>
        <p><i class="fa-solid fa-location-dot"></i> <?=htmlspecialchars($hotel['area'])?></p>
        <div><span><?=htmlspecialchars($hotel['style'])?></span><a href="contact.php?stay=<?=urlencode($hotel['name'])?>">Ask about this stay <b>↗</b></a></div>
      </article><?php endforeach; ?>
    </div>
    <p class="stay-note">Hotel suggestions are for trip inspiration; final availability and rates are confirmed personally by our travel team.</p>
  </div>
</section>
<?php endif; ?>

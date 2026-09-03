<?php
$dayStmt=$db->prepare('SELECT * FROM package_itinerary WHERE package_id=? ORDER BY day_number');
$dayStmt->execute([$package_id]);
$itinerary=$dayStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if($itinerary): ?>
<section class="route-section">
  <div class="catalog-shell">
    <div class="route-heading"><span>YOUR JOURNEY, DAY BY DAY</span><h2>One road.<br><em><?=count($itinerary)?> unforgettable days.</em></h2><p>Tap any day to open the full plan. Every route can be slowed down, extended or completely reimagined.</p></div>
    <div class="route-map">
      <?php foreach($itinerary as$i=>$day): ?><article class="route-day <?=$i===0?'open':''?>" style="--day-image:url('<?=htmlspecialchars($day['image'])?>')">
        <button type="button" aria-expanded="<?=$i===0?'true':'false'?>">
          <span class="route-dot"><b><?=str_pad((string)$day['day_number'],2,'0',STR_PAD_LEFT)?></b></span>
          <span class="route-title"><small>DAY <?=$day['day_number']?> · <?=htmlspecialchars($day['location'])?></small><strong><?=htmlspecialchars($day['title'])?></strong></span>
          <span class="route-distance"><?=htmlspecialchars($day['distance_note'])?></span><i>+</i>
        </button>
        <div class="route-panel"><div class="route-photo" role="img" aria-label="<?=htmlspecialchars($day['location'])?>"></div><div><span>THE PLAN</span><p><?=htmlspecialchars($day['description'])?></p><a href="contact.php?package=<?=urlencode($package['name'])?>">Personalise this day ↗</a></div></div>
      </article><?php endforeach; ?>
    </div>
  </div>
</section>
<script>document.querySelectorAll('.route-day>button').forEach(button=>button.addEventListener('click',()=>{const day=button.parentElement;const open=day.classList.toggle('open');button.setAttribute('aria-expanded',open?'true':'false')}));</script>
<?php endif; ?>

<?php
$page_title='Photography Credits - Serendib Pathways';
$creditsFile=__DIR__.'/assets/destinations/real/credits.json';
$credits=is_file($creditsFile)?json_decode((string)file_get_contents($creditsFile),true):[];
$experienceCreditsFile=__DIR__.'/assets/experiences/credits.json';
if (is_file($experienceCreditsFile)) {
    $credits=array_merge($credits, json_decode(ltrim(file_get_contents($experienceCreditsFile), "\xEF\xBB\xBF"),true) ?: []);
}
include 'includes/header.php';
?>
<section class="relative bg-green-900 text-white py-28"><div class="container mx-auto px-4 text-center"><span class="eyebrow">WITH THANKS</span><h1 class="text-5xl md:text-6xl font-bold">Photography credits</h1><p class="mt-5 text-white/70">Destination and experience photography sourced from Wikimedia Commons. Some experience photos illustrate the activity in other locations.</p></div></section>
<section class="py-16 bg-gray-50"><div class="container mx-auto px-4 max-w-5xl"><div class="grid md:grid-cols-2 gap-4">
<?php foreach($credits as$credit): ?><article class="bg-white border border-gray-200 rounded-xl p-5"><small class="text-green-700 font-bold tracking-widest"><?=htmlspecialchars($credit['destination']??'')?></small><h2 class="font-bold text-lg mt-2"><?=htmlspecialchars(preg_replace('/^File:/','',$credit['title']??''))?></h2><p class="text-gray-600 text-sm mt-2">By <?=htmlspecialchars($credit['artist']??'Wikimedia contributor')?> · <?=htmlspecialchars($credit['license']??'Wikimedia Commons')?></p><?php if(!empty($credit['source'])):?><a class="inline-block mt-3 text-sm font-bold text-green-700" href="<?=htmlspecialchars($credit['source'])?>" target="_blank" rel="noopener">View source ↗</a><?php endif?><?php if (!empty($credit['license_url'])): ?> <a class="inline-block mt-3 ml-3 text-sm text-green-700" href="<?=htmlspecialchars($credit['license_url'], ENT_QUOTES)?>" target="_blank" rel="noopener">View license</a><?php endif; ?></article><?php endforeach?>
</div></div></section>
<?php include 'includes/footer.php'; ?>

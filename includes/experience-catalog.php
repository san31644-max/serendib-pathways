<?php
// File-backed experiences deploy with the site, without a production database import.
function experience_catalog(PDO $db): array
{
    $types = $db->query('SELECT activity_type_id, activity_type_name FROM activity_types ORDER BY activity_type_name')->fetchAll(PDO::FETCH_ASSOC);
    $typeIds = [];
    foreach ($types as $type) {
        $typeIds[strtolower($type['activity_type_name'])] = (string) $type['activity_type_id'];
    }
    $curated = json_decode(file_get_contents(__DIR__ . '/experience-catalog.json'), true);
    $credits = json_decode(ltrim(file_get_contents(__DIR__ . '/../assets/experiences/credits.json'), "\xEF\xBB\xBF"), true);
    $photos = [];
    foreach ($credits as $credit) {
        $photos[$credit['destination']] = $credit['file'];
    }
    foreach ($curated as &$experience) {
        $key = strtolower($experience['category_name']);
        if (!isset($typeIds[$key])) {
            $typeIds[$key] = 'experience-' . preg_replace('/[^a-z0-9]+/', '-', $key);
            $types[] = ['activity_type_id' => $typeIds[$key], 'activity_type_name' => $experience['category_name']];
        }
        $experience['activity_type_id'] = $typeIds[$key];
        $experience['image'] = $photos[$experience['name']] ?? '';
        $experience['inclusions'] = '';
        $experience['difficulty'] = '';
    }
    unset($experience);
    $existing = $db->query('SELECT a.*, t.activity_type_name AS category_name FROM activities a LEFT JOIN activity_types t ON a.activity_type_id = t.activity_type_id')->fetchAll(PDO::FETCH_ASSOC);
    $normalize = static function ($name) {
        return preg_replace('/[^a-z0-9]/', '', strtolower(explode(' – ', $name)[0]));
    };
    $names = array_map($normalize, array_column($curated, 'name'));
    $existing = array_filter($existing, static function ($row) use ($names, $normalize) {
        return !in_array($normalize($row['name']), $names, true);
    });
    $activities = array_merge(array_values($existing), $curated);
    usort($activities, static function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
    usort($types, static function ($a, $b) { return strcasecmp($a['activity_type_name'], $b['activity_type_name']); });
    return ['activities' => $activities, 'types' => $types];
}

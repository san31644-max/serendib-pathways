<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['admin_id'])) { http_response_code(403); exit('Admin login required'); }
$library = require '../config/photo-library.php';
$db = (new Database())->getConnection();
$find = $db->prepare('SELECT id,description,location,category_id,highlights FROM destinations WHERE name=?');
$insert = $db->prepare('INSERT INTO destinations(name,location,description,image,category_id,highlights,gallery_image1,gallery_image2,gallery_image3,gallery_image4) VALUES(?,?,?,?,?,?,?,?,?,?)');
$update = $db->prepare('UPDATE destinations SET location=?,description=?,image=?,category_id=?,highlights=?,gallery_image1=?,gallery_image2=?,gallery_image3=?,gallery_image4=? WHERE id=?');
$count=0;
foreach ($library as $name=>$item) {
  $files=glob('../assets/destinations/user/'.$item['slug'].'/*.{jpg,jpeg,png,webp,avif,gif,JPG,JPEG,PNG}',GLOB_BRACE); sort($files,SORT_NATURAL);
  if (!$files) continue;
  $paths=array_map(fn($p)=>ltrim(str_replace('..\\','',str_replace('../','',str_replace('\\','/',$p))),'/'),$files);
  $find->execute([$name]); $existing=$find->fetch(PDO::FETCH_ASSOC);
  $base=$existing ?: [];
  $location=$item['location'] ?? ($base['location'] ?? 'Sri Lanka');
  $description=$item['description'] ?? ($base['description'] ?? 'Discover this remarkable Sri Lankan destination.');
  $category=$item['category'] ?? ($base['category_id'] ?? 1);
  $highlights=$item['highlights'] ?? ($base['highlights'] ?? 'Culture\nLandscape\nLocal experiences');
  $images=array_pad(array_slice($paths,0,5),5,'');
  if ($existing) $update->execute([$location,$description,$images[0],$category,$highlights,$images[1],$images[2],$images[3],$images[4],$existing['id']]);
  else $insert->execute([$name,$location,$description,$images[0],$category,$highlights,$images[1],$images[2],$images[3],$images[4]]);
  $count++;
}
header('Content-Type: text/plain; charset=utf-8'); echo "Synchronized $count photo destinations";

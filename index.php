<? 
#header('Content-Type: text/plain');

function aggregate_folder($dir, $ignore){
  $file_feed = array();  
  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
      while (($file = readdir($dh)) !== false) {
        if(!in_array($file, $ignore)){ 
          $file_item = array();
          $file_item['file'] = $file;
          $file_item['dir'] = $dir;
          $file_item['mime'] = mime_content_type($dir . $file);
          $file_item['time'] = filemtime($dir . $file);
          $file_item['path'] = str_replace('index.php', '',  $_SERVER['SCRIPT_NAME']);
          if($dir == './'){
            $file_item['url'] = 'http://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . $file_item['file'];
            $file_item['uri'] = 'http://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . '?' . $file_item['file'];
            $file_item['feed'] = 'feed://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . '?' . $file_item['file'];
          }
          else{
            $file_item['url'] = 'http://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . $file_item['dir'] . $file_item['file'];
            $file_item['uri'] = 'http://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . '?' . $file_item['dir'] . $file_item['file'];
            $file_item['feed'] = 'feed://'. $_SERVER['SERVER_NAME'] . $file_item['path'] . '?' . $file_item['dir'] . $file_item['file'];
          }
          $file_feed[$file_item['time'].$file_item['file']] = $file_item;
        }
      }
      closedir($dh);
    }
  }
  return $file_feed;
}

function get_latest_time($file_feed){
  $max = 0;
  foreach ($file_feed as $id => $item)
    $max = max($item['time'], $max);
  return $max;
}

function print_rss($file_feed, $title = 'DirCaster'){
  if($title == './')
    $title = 'DirCaster';
  ?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" version="2.0">
    <channel>
    <title><?=$title?></title>
      <link>http://<?echo $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']?></link>
      <language>de-de</language>
      <pubDate><?=strftime("%a, %d %b %Y %H:%M:%S GMT",get_latest_time($file_feed))?></pubDate>

<?php
  krsort($file_feed);
  foreach ($file_feed as $id => $item){
    if($item['mime'] == 'directory'){
      print_folder($item);
      //print_item_raw($item); echo "\n".$id; echo "\n \n \n ";
    }
    else{
      print_item($item);
      //print_item_raw($item); echo "\n".$id; echo "\n \n \n ";
    }
    
  }
  ?>

  </channel>
</rss><?php
}

function print_item_raw($item){
  echo $item['file']."\n";
  echo $item['mime']."\n";
  echo $item['time']."\n";
  echo $item['path']."\n";
  echo $item['uri']."\n";
}

function print_item($item){ ?>
    <item>
      <title><?php echo $item['file']?> </title>
      <description><?php echo "filename : " . $item['file']; ?></description>
      <link><?echo $item['url']?></link>
      <enclosure url="<?echo $item['url']?>" type="<?=$item['mime']?>" />
      <pubDate><?=strftime("%a, %d %b %Y %H:%M:%S GMT",$item['time'])?></pubDate>
    </item>

<?  }

function print_folder($item){ ?>
    <item>
      <title><?php echo $item['file']?> </title>
      <description><?echo $item['uri']?></description>
      <guid isPermaLink="false"><?echo $item['url']?></guid>
      <link><?echo $item['feed']?></link>
      <enclosure url="<?echo $item['uri']?>" type="<?=$item['mime']?>" />
      <pubDate><?=strftime("%a, %d %b %Y %H:%M:%S GMT",$item['time'])?></pubDate>
    </item>
<?  }



$dir = "./";
$ignore = array('.', '..', '.index.php.swp', 'index.php');

$sub_dir = array_keys($_GET);
$sub_dir = $sub_dir[0];

if($sub_dir)
  $dir = $sub_dir.'/';

$file_feed = aggregate_folder($dir, $ignore);
print_rss($file_feed, $dir);



?>


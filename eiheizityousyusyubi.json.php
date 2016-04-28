<?php
header("Content-Type: application/json; charset=utf-8");

$t = new DateTime();
$lines = file('./eiheizityousyusyubi_tsv.txt', FILE_IGNORE_NEW_LINES);
//ID,                                    
$json = array();
foreach ($lines as $line) {
  if ( !preg_match('/^#/', $line) ) {
    $a = explode("\t", $line);
    $json[$a[0]] = array (
      'id'       => $a[0], // ID
      'column'   => $a[1], // 行
      'initial'  => $a[2], // 音
      'address'  => $a[3], // 町・地区名
      'phonetic' => $a[4], // 読み仮名
      'burnable'         => $a[5], // 燃えるゴミ
      'plastic'          => $a[6], // プラスチック
      'nonburnable'      => $a[7], // 燃えないごみ
      'bottles'          => $a[8], // カン・ビン等
      'dates' => array (
        array ( 'id' => 'burnable',     'title' => '燃えるゴミ',    'date' => parse($a[5]) ),
        array ( 'id' => 'plastic',      'title' => 'プラスチック',  'date' => parse($a[6]) ),
        array ( 'id' => 'nonburnable',  'title' => '燃えないごみ',  'date' => parse($a[7]) ),
        array ( 'id' => 'bottles',      'title' => 'カン・ビン等',  'date' => parse($a[8]) )
      ),
     'keywords' => $a[9],  // キーワード
     'note'     => $a[10]  // 備考
    );
  }
}

echo sprintf("eiheizityousyusyubi(%s)",json_encode($json));
exit;

function parse($s) {
  global $t;
  $o = array('', 'first', 'second', 'third', 'fourth', 'fifth');
  switch(true) {
    case preg_match('/^[日月火水木金土]$/u', $s): //ex.金
      $d = array(
        "next ".getDayOfTheWeek($s)
      );
      break;
    case preg_match('/^毎週([日月火水木金土])・([日月火水木金土])$/u', $s, $m): //ex.毎週月・木
      $d = array(
        "next ".getDayOfTheWeek($m[1]),
        "next ".getDayOfTheWeek($m[2])
      );
      break;
    case preg_match('/^第([12345])・([12345])([日月火水木金土])$/u', $s, $m): //ex.第1・3火
      $d = array(
        $o[$m[1]]." ".getDayOfTheWeek($m[3])." of this month",
        $o[$m[2]]." ".getDayOfTheWeek($m[3])." of this month",
        $o[$m[1]]." ".getDayOfTheWeek($m[3])." of next month",
        $o[$m[2]]." ".getDayOfTheWeek($m[3])." of next month"
      );
      break;
    default:
      return $s;
  }
  $d = array_map( 'parseDate', $d );
  sort($d);
  foreach ($d as $date) {
    if ($date > $t) {
      return $date->format(DateTime::ATOM);
      break;
    }
  };
}

function parseDate($d) {
  return new DateTime($d);
}

function getDayOfTheWeek($s) {
  $w = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
  return $w[mb_strpos("日月火水木金土", $s, 0, "utf-8")];
}
?>

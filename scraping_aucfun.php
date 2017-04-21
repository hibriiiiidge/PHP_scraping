<?php
  // phpQueryの読み込み
  require_once("phpQuery-onefile.php");

  //targetWordsに関する処理
  //@TODO $_GET['query']のバリデーション
  $tw = (string)filter_input(INPUT_GET, 'targetWords');
  $halfWidthSpaceQ = mb_convert_kana($tw, 's');
  $arrayQ = preg_split('/[\s]+/', $halfWidthSpaceQ, -1, PREG_SPLIT_NO_EMPTY);

  $arrayPreQ = array(); //検索URL生成直前のクエリワードの配列
  $arrayCvtQ = array(); //検索URL生成完成したクエリワードの配列

  //配列の各要素が英数字かどうかを確認する処理
  foreach ($arrayQ as $val) {
    if(!is_AlpNum($val)){
      //英数字以外の場合は16進数のEUC-JPに変換する処理
      $euc16Val = bin2hex(mb_convert_encoding($val, "EUC-JP"));
      //文字列を2文字毎に"."で区切る処理 ex) a1b2c3d4e5 -> a1.b2.c3.d4.e5
      $sprtVal  = wordwrap($euc16Val, 2, ".", true);
      //先頭に"."をつける処理 ex)a1.b2.c3.d4.e5 -> .a1.b2.c3.d4.e5
      $cvtVal  = ".".$sprtVal;
      array_push($arrayPreQ, $cvtVal);
    }
    else{
      array_push($arrayPreQ, $val);
    }
  }

  //配列の先頭の配列以外に"20"をつける処理 20->16進数のEUC-JPで半角スペース
  for ($i=0; $i < count($arrayPreQ); $i++) {
    if($i==0){
      array_push($arrayCvtQ, $arrayPreQ[$i]);
    }
    else{
      $add20Val = "20".$arrayPreQ[$i];
      array_push($arrayCvtQ, $add20Val);
    }
  }
  //配列を"."でつなぎ文字列に変換する処理
  $q = implode(".", $arrayCvtQ);

  //pageNumに関する処理
  //@TODO $_GET['pageNum']のバリデーションをしっかりとすべき
  $pn = (string)filter_input(INPUT_GET, 'pageNum');
  $p = $_GET['pageNum'] ? "p=".$pn."&" : "";

  //ターゲットURL
  $targetUrl = "http://aucfan.com/search1/q-".$q."/s-mix/?".$p."o=p2";
  // HTMLの取得
  $htmlData = file_get_contents($targetUrl);
  $doc = phpQuery::newDocument($htmlData);

  //hit総数取得
  $sortCount = $doc[".main"]->find(".sort_count")->text();
  //$jsonData[] = ['allNum' => $sortCount];
  //「約*件」の抜き出し
  //preg_match_all('/約(.*)件/', $sortCount, $allNum, PREG_PATTERN_ORDER);
  //$jsonData[] = ['allNum' => $allNum[1][0]];

  //results_bidの項目をforeachで回す
  foreach ($doc[".main"]->find(".results_bid") as $items) {
    sleep(1);
    $itemTitle  = pq($items)->find(".item_title")->text();
    $itemPrice  = pq($items)->find(".item_price")->text();
    $itemImg    = pq($items)->find('img')->attr('data-src-original');
    if($itemPrice){
      $jsonData['items'][] = ['title' => $itemTitle, 'price' => $itemPrice, 'img' => $itemImg];
    }
  }

  $jsonData['keyInfo'][] = ['allNum' => $sortCount, 'pageNum' => $pn];

  //json を出力
  header(" Content-Type:application/json; charset=utf-8");
  echo json_encode($jsonData, JSON_UNESCAPED_UNICODE);

  //英数字かどうか確認する処理
  function is_AlpNum($str){
    return preg_match("/[a-zA-Z0-9]+$/", $str) ? true : false;
  }

  //EUCに変換する処理
  function toEUC($str){
    return mb_convert_encoding($str, "UTF-8", "EUC-JP");
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script type="text/javascript">
      $(function(){
        $(document).on("click", ".getBtn", function(){
          var targetWords = $("#targetWords").val();  //検索対象のワード
          //@TODO バリデーション とりま簡易バリデ
          if(contChk(targetWords)==false){
            alert("検索ワードを確認してください。");
            return false;
          }

          var pageNum = $(".more").data("round"); //ページナンバー（何ページ目を次に表示するか 1ページ目 null）
          if(!pageNum){$("#result").html('');}        //検索対象のワードを再度設定した場合、前検索結果を削除する処理

          $.ajax({
            url: 'http://13.228.5.123/scraping_aucfun.php',
            type: 'get',
            dataType: 'json',
            data:{
              targetWords : targetWords,
              pageNum     : pageNum
            }
          })
          .done(function(data){
            //data.itemsの中に商品の配列、data.keyInfoの中にページナンバーとhit商品数
            // console.log(data);
            $.each(data.keyInfo, function(j, keyVal){
              //ヒット商品数
              $("#result").append($('<div>').text(keyVal.allNum).attr({class:'allNum'}));
              //ページナンバー
              pN = keyVal.pageNum;
            });
            //1ページ目か2ページ目以降かを判断
            if(pN){
              //2ページ目以降
              targetResult = "#result" + pN; //検索結果を表示するid
              pN = Number(pN) + 1; //次ページのページナンバー
            }
            else{
              //1ページ目
              targetResult = "#result";
              pN = 2;
            }

            $.each(data.items, function(i, val){
              //商品情報を何番目の".item"としてappendする
              addNum = 50 * (pN-2);
              $(targetResult).append($('<div>').attr('class', 'item'));
              $(".item").eq(i+addNum).append($('<div>').text(val.title).attr({class:'itemTitle'}));
              $(".item").eq(i+addNum).append($('<div>').text(val.price).attr({class:'itemPrice'}));
              $(".item").eq(i+addNum).append($('<img>').attr({src:val.img}));
            });
            //商品が50商品だったら (50商品が1ページに表示される最大値)
            //@TODO 検索商品が50商品丁度だった場合に"更に読み込む"が表示されるので非表示に
            if(data.items.length == 50){
              $(targetResult).append($('<button>').text("▼更に読み込む").attr({class: 'more getBtn', 'data-round': pN, id: 'moreBtn'+pN}));
              //前ページの"更に〜"ボタンを消去する
              var rmpN = Number(pN) - 1;
              var rmBtn = '#moreBtn' + rmpN;
              $(rmBtn).remove();
              //次ページの商品一覧を表示する #resultを生成
              $(targetResult).after($('<div>').attr({id: 'result' + pN}));
            }
          })
          .fail(function(data){
            alert("ERROR");
            console.log(data);
          });
        });

        function contChk(val){
          if(val.match(/[-_.!~*\'();\/?:\@&=+\$,%#<>]+/)){
            return false;
          }
            return true;
        }
      });
    </script>
    <title>TEST SCRAPING TO AUCFUN</title>
  </head>
  <body>
    <h1>SCRAPING PAGE -AUCFUN-</h1>
    <form class="">
      <input type="text" name="targetWords" id="targetWords" value="">
      <button type="button" id="getBtn" class="getBtn" name="button">GET!!</button>
    </form>
    <div id="result"></div>
  </body>
</html>
